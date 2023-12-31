<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

use App\Models\Document;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Category;
use App\Models\DocumentType;
use App\Models\Sender;
use App\Models\Hei;
use App\Models\Nga;
use App\Models\ChedOffice;
use App\Models\Division;
use App\Models\DocumentLog;
use App\Models\DocumentAssignation;
use App\Models\Profile;

use App\Notifications\DocumentForwarded;
use App\Notifications\DocumentForwardedTo;
use App\Notifications\DocumentAcknowledged;
use App\Notifications\DocumentApproved;
use App\Notifications\DocumentRejected;
use App\Notifications\DocumentReleased;
use App\Notifications\DocumentActedOn;

class DocumentController extends Controller
{
    public function addDocument (Request $request) {
        $user = $request->user();

        $requestData = $request->only(['document_type_id', 'date_received', 'receivable_type', 'receivable_id', 'receivable_name', 'description', 'category_id', 'assign_to']);
        $requestFile = $request->file('attachment');

        $validator = Validator::make(array_merge($requestData, [
            'attachment' => $requestFile
        ]), [
            'document_type_id' => 'required|integer',
            'attachment' => 'nullable|file',
            'date_received' => 'required|date',
            'receivable_type' => 'required|string|in:HEIs,NGAs,CHED Offices,Others',
            'receivable_name' => 'required_if:receivable_type,Others',
            'receivable_id' => 'required_if:receivable_type,HEIs,NGAs,CHED Offices|nullable|integer',
            'description' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
            'assign_to' => 'array|nullable|present|max:1',
            'assign_to.*' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $documentType = DocumentType::find($requestData['document_type_id']);
        if (!$documentType) {
            return response()->json(['message' => 'Document type not found'], 404);
        }

        $category = Category::find($requestData['category_id']);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($requestData['assign_to']) {
            $users = User::with(['role', 'role.division'])->whereIn('id', $requestData['assign_to'])->get();
            if ($users->count() !== count($requestData['assign_to'])) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            if ($category->is_assignable) {
                $filtered = $users->filter(function ($toAssignUser) {
                    return $toAssignUser->role->division && (
                        $toAssignUser->role->division->description === 'Administrative' ? (
                            $toAssignUser->role->level > 4
                        ) : $toAssignUser->role->division->description === 'Technical' &&
                            $toAssignUser->role->level > 5
                    );
                });
                if ($filtered->count() > 0) {
                    return response()->json(['message' => 'Unable to assign to user.'], 409);
                }
            }
        }

        $dateReceived = Carbon::parse($requestData['date_received']);
        $latestDocument = Document::where('document_type_id', $documentType->id)->whereYear('date_received', $dateReceived->format('Y'))->orderBy('series_no', 'DESC')->first();
        $seriesNo = $latestDocument ? $latestDocument->series_no+1 : 1;
        $trackingNo = $dateReceived->format('y') . '-' . $documentType->code . '-' . str_pad($seriesNo, 4, '0', STR_PAD_LEFT);

        try {
            DB::beginTransaction();

            $receivable = null;
            switch ($requestData['receivable_type']) {
                case 'HEIs':
                    $receivable = Hei::find($requestData['receivable_id']);
                    break;
                case 'NGAs':
                    $receivable = Nga::find($requestData['receivable_id']);
                    break;
                case 'CHED Offices':
                    $receivable = ChedOffice::find($requestData['receivable_id']);
                    break;
                default:
                    $receivable = 'Others';
            }

            if (!$receivable) {
                return response()->json(['message' => 'Received from not found'], 404);
            }

            $sender = new Sender();
            if ($receivable === 'Others') {
                $sender->name = $requestData['receivable_name'];
                $sender->receivable_type = null;
                $sender->receivable_id = null;
            } else {
                $sender->name = null;
                $sender->receivable()->associate($receivable);
            }
            $sender->save();

            $document = new Document([
                'user_id' => $user->id,
                'document_type_id' => $documentType->id,
                'tracking_no' => $trackingNo,
                'date_received' => $requestData['date_received'],
                'sender_id' => $sender->id,
                'description' => $requestData['description'],
                'category_id' => $requestData['category_id'],
                'series_no' => $seriesNo
            ]);

            if ($document->save()) {

                if ($requestFile) {
                    $hash = Str::random(40);
                    $ext = $requestFile->getClientOriginalExtension();
                    $fileName = $requestFile->storeAs('/'.$document->id, $hash.'.'.$ext, 'document_files');
                    $attachment = new Attachment([
                        'document_id' => $document->id,
                        'file_name' => $fileName,
                        'file_title' => $requestFile->getClientOriginalName()
                    ]);
                    $attachment->save();
                }

                if (!$category->is_assignable) {
                    $assignTo = User::whereHas('role', function ($query) {
                        $query->where('level', 2);
                    })
                    ->first();
                    $log      = new DocumentAssignation([
                        'assigned_id' => $assignTo,
                    ]);
                    $document->assign()->save($log);
                } else if ($requestData['assign_to']) {
                    $logs = [];
                    foreach ($requestData['assign_to'] as $assignTo) {
                        $logs[] = new DocumentAssignation([
                            'assigned_id' => $assignTo
                        ]);
                    }
                    if (!empty($logs)) {
                        $document->assign()->saveMany($logs);
                    }
                }

                $document->load(['user', 'documentType', 'attachments']);
                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully added a document.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to add a document.'], 400);
    }

    public function forwardDocumentUponReceive (Request $request) {
        $user = $request->user();

        $requestData = $request->only(['document_type_id', 'date_received', 'receivable_type', 'receivable_id', 'receivable_name', 'description', 'category_id', 'assign_to']);
        $requestFile = $request->file('attachment');

        $validator = Validator::make(array_merge($requestData, [
            'attachment' => $requestFile
        ]), [
            'document_type_id' => 'required|integer',
            'attachment' => 'nullable|file',
            'date_received' => 'required|date',
            'receivable_type' => 'required|string|in:HEIs,NGAs,CHED Offices,Others',
            'receivable_name' => 'required_if:receivable_type,Others',
            'receivable_id' => 'required_if:receivable_type,HEIs,NGAs,CHED Offices|nullable|integer',
            'description' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
            'assign_to' => 'array|nullable|present|max:1',
            'assign_to.*' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $documentType = DocumentType::find($requestData['document_type_id']);
        if (!$documentType) {
            return response()->json(['message' => 'Document type not found'], 404);
        }

        $category = Category::find($requestData['category_id']);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        if ($requestData['assign_to']) {
            $users = User::with(['role', 'role.division'])->whereIn('id', $requestData['assign_to'])->get();
            if ($users->count() !== count($requestData['assign_to'])) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            if ($category->is_assignable) {
                $filtered = $users->filter(function ($toAssignUser) {
                    return $toAssignUser->role->division && (
                        $toAssignUser->role->division->description === 'Administrative' ? (
                            $toAssignUser->role->level > 4
                        ) : $toAssignUser->role->division->description === 'Technical' &&
                            $toAssignUser->role->level > 5
                    );
                });
                if ($filtered->count() > 0) {
                    return response()->json(['message' => 'Unable to assign to user.'], 409);
                }
            }
        }

        $dateReceived = Carbon::parse($requestData['date_received']);
        $latestDocument = Document::where('document_type_id', $documentType->id)->whereYear('date_received', $dateReceived->format('Y'))->orderBy('series_no', 'DESC')->first();
        $seriesNo = $latestDocument ? $latestDocument->series_no+1 : 1;
        $trackingNo = $dateReceived->format('y') . '-' . $documentType->code . '-' . str_pad($seriesNo, 4, '0', STR_PAD_LEFT);

        try {
            DB::beginTransaction();

            $receivable = null;
            switch ($requestData['receivable_type']) {
                case 'HEIs':
                    $receivable = Hei::find($requestData['receivable_id']);
                    break;
                case 'NGAs':
                    $receivable = Nga::find($requestData['receivable_id']);
                    break;
                case 'CHED Offices':
                    $receivable = ChedOffice::find($requestData['receivable_id']);
                    break;
                default:
                    $receivable = 'Others';
            }

            if (!$receivable) {
                return response()->json(['message' => 'Received from not found'], 404);
            }

            $sender = new Sender();
            if ($receivable === 'Others') {
                $sender->name = $requestData['receivable_name'];
                $sender->receivable_type = null;
                $sender->receivable_id = null;
            } else {
                $sender->name = null;
                $sender->receivable()->associate($receivable);
            }
            $sender->save();

            $document = new Document([
                'user_id' => $user->id,
                'document_type_id' => $documentType->id,
                'tracking_no' => $trackingNo,
                'date_received' => $requestData['date_received'],
                'sender_id' => $sender->id,
                'description' => $requestData['description'],
                'category_id' => $requestData['category_id'],
                'series_no' => $seriesNo
            ]);

            if ($document->save()) {

                if ($requestFile) {
                    $hash = Str::random(40);
                    $ext = $requestFile->getClientOriginalExtension();
                    $fileName = $requestFile->storeAs('/'.$document->id, $hash.'.'.$ext, 'document_files');
                    $attachment = new Attachment([
                        'document_id' => $document->id,
                        'file_name' => $fileName,
                        'file_title' => $requestFile->getClientOriginalName()
                    ]);
                    $attachment->save();
                }

                $document->load('category');

                if (!$category->is_assignable) {
                    $fromUser = User::whereHas('role', function ($query) {
                                    $query->where('level', 1);
                                })
                                ->first();
                    $assignTo = User::whereHas('role', function ($query) {
                        $query->where('level', 2);
                    })
                    ->first();
                    $assign      = new DocumentAssignation([
                        'assigned_id' => $assignTo->id,
                    ]);

                    $log      = new DocumentLog([
                        'to_id' => $assignTo->id,
                    ]);
                    $document->assign()->save($assign);
                    $document->logs()->save($log);

                    Notification::send([$assignTo], new DocumentForwarded($document->toArray(), $log->toArray(), $fromUser->profile->toArray(), $assignTo->profile->toArray()));
                } else if ($requestData['assign_to']) {
                    $notifications = [];

                     $assignations = [];
                    foreach($requestData['assign_to'] as $assignTo) {
                        $assignations[] = new DocumentAssignation([
                            'assigned_id' => $assignTo,
                        ]);
                    }

                    $logs = [];

                    $divisions = Division::with([
                        'role' => function($query) {
                            $query -> where('level', 3);
                        },
                        'role.user'
                    ])->get();

                    $fromUser = User::whereHas('role', function ($query) {
                                    $query->where('level', 1);
                                })
                                ->first();

                    $director = User::whereHas('role', function ($query) {
                                    $query->where('level', 2);
                                })
                                ->first();

                    $log = new DocumentLog();
                    $log->to_id = $director->id;
                    $logs[] = $log;
                    Notification::send([$director,$fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $fromUser->profile->toArray(), $director->profile->toArray()));

                    foreach($divisions as $division) {
                        $filteredUsers = $users->filter(function ($value, int $key) use($division) {
                            return $value->role->division_id === $division->id;
                        });

                        if($filteredUsers->count() > 0) {
                             $log = new DocumentLog();
                            if ($filteredUsers->where('id', $division->role->user->id)->first()) {
                                $log->assigned_id = $division->role->user->id;
                            }
                            $log->to_id = $division->role->user->id;
                            $log->from_id = $director->id;
                            $logs[] = $log;
                            Notification::send([$director, $division->role->user, $fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $director->profile->toArray(), $division->role->user->profile->toArray()));

                            $subordinateLevel = $division->role->user->role->level+1;

                            $filteredLevel = $filteredUsers->filter(function ($value) use ($subordinateLevel) {
                                return $value->role->level === $subordinateLevel;
                            });

                            $superior = $division->role->user;

                            if ($filteredLevel->count() === 0) {
                                $subordinate = User::whereHas('role', function ($query) use ($subordinateLevel, $division) {
                                    $query->where('level', $subordinateLevel)->where('division_id', $division->id);
                                })->first();

                                $filtered = $filteredUsers->filter(function ($value) use ($subordinateLevel) {
                                    return $value->role->level > $subordinateLevel;
                                });

                                 if ($filtered->count() > 0) {
                                    $log = new DocumentLog();
                                    $log->to_id = $subordinate->id;
                                    $log->from_id = $superior->id;
                                    $logs[] = $log;
                                    Notification::send([$director, $fromUser, $superior, $subordinate], new DocumentForwarded($document->toArray(), $log->toArray(), $superior->profile->toArray(), $subordinate->profile->toArray()));

                                    $superior = $subordinate;
                                 }

                            }

                            foreach($filteredUsers as $assignTo) {
                                if ($assignTo->id !== $superior->id) {
                                    $log = new DocumentLog();
                                    $log->assigned_id = $assignTo->id;
                                    $log->to_id = $assignTo->id;
                                    $log->from_id = $superior->id;
                                    $logs[] = $log;
                                    Notification::send([$director, $fromUser, $superior, $assignTo], new DocumentForwarded($document->toArray(), $log->toArray(), $superior->profile->toArray(), $assignTo->profile->toArray()));
                                }
                            }
                        }
                    }

                    $document->assign()->saveMany($assignations);
                    $document->logs()->saveMany($logs);
                }

                $document->load(['user', 'documentType', 'attachments']);
                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully added and forwarded the document.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to add a document.'], 400);
    }

    public function editDocument (Request $request, $id) {
        $user = $request->user();

        $requestData = $request->only(['document_type_id','attachment', 'date_received', 'receivable_type', 'receivable_name', 'receivable_id', 'description', 'category_id', 'assign_to' ]);
        $requestFile = $request->file('attachment');

        $validator = Validator::make(array_merge($requestData, [
            'attachment' => $requestFile
        ]), [
            'document_type_id' => 'required|integer',
            'attachment' => 'nullable|file',
            'date_received' => 'required|date',
            'receivable_type' => 'required|string|in:HEIs,NGAs,CHED Offices,Others',
            'receivable_name' => 'required_if:receivable_type,Others',
            'receivable_id' => 'required_if:receivable_type,HEIs,NGAs,CHED Offices|nullable|integer',
            'description' => 'required|string',
            'category_id' => 'required|integer',
            'assign_to' => 'array|nullable|max:1',
            'assign_to.*' => 'integer|min:1|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $documentType = DocumentType::find($requestData['document_type_id']);
        if (!$documentType) {
            return response()->json(['message' => 'Document type not found'], 404);
        }

        $category = Category::find($requestData['category_id']);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $seriesNo = $document->series_no;
        $trackingNo = $document->tracking_no;

        $dateReceived = Carbon::parse($requestData['date_received']);
        $dbDateReceived = Carbon::parse($document->date_received);
        if (!$dateReceived->isSameDay($dbDateReceived) || $document->document_type_id !== $documentType->id) {
            $latestDocument = Document::where('document_type_id', $documentType->id)->whereYear('date_received', $dateReceived->format('Y'))->orderBy('series_no', 'DESC')->first();
            $seriesNo = $latestDocument ? $latestDocument->series_no+1 : 1;
            $trackingNo = $dateReceived->format('y') . '-' . $documentType->code . '-' . str_pad($seriesNo, 4, '0', STR_PAD_LEFT);
        }

        try {
            DB::beginTransaction();

            $receivable = null;
            switch ($requestData['receivable_type']) {
                case 'HEIs':
                    $receivable = Hei::find($requestData['receivable_id']);
                    break;
                case 'NGAs':
                    $receivable = Nga::find($requestData['receivable_id']);
                    break;
                case 'CHED Offices':
                    $receivable = ChedOffice::find($requestData['receivable_id']);
                    break;
                default:
                    $receivable = 'Others';
            }

            if (!$receivable) {
                return response()->json(['message' => 'Received from not found'], 404);
            }

            $sender = $document->sender;
            if ($receivable === 'Others') {
                $sender->name = $requestData['receivable_name'];
                $sender->receivable_type = null;
                $sender->receivable_id = null;
            } else {
                $sender->name = null;
                $sender->receivable()->associate($receivable);
            }
            $sender->save();

            $document->user_id = $user->id;
            $document->document_type_id = $documentType->id;
            $document->tracking_no = $trackingNo;
            $document->date_received = $requestData['date_received'];
            $document->description = $requestData['description'];
            $document->category_id = $requestData['category_id'];
            $document->series_no = $seriesNo;

            if ($document->save()) {

                if ($requestFile) {
                    Storage::disk('document_files')->deleteDirectory($document->id);
                    $hash = Str::random(40);
                    $ext = $requestFile->getClientOriginalExtension();
                    $fileName = $requestFile->storeAs('/'.$document->id, $hash.'.'.$ext, 'document_files');
                    $attachment = new Attachment([
                        'document_id' => $document->id,
                        'file_name' => $fileName,
                        'file_title' => $requestFile->getClientOriginalName()
                    ]);

                    $attachment->save();
                }

               if (!$category->is_assignable) {
                    $assignTo = User::whereHas('role', function ($query) {
                        $query->where('level', 2);
                    })
                    ->first();

                    $log      = new DocumentAssignation([
                        'assigned_id' => $assignTo,
                    ]);
                    $document->assign()->delete();
                    $document->assign()->save($log);
                } else if ($requestData['assign_to']) {
                    if ($category -> is_assignable) {
                        $logs = [];
                        foreach($requestData['assign_to'] as $assignTo) {
                            $log = new DocumentAssignation();
                            $log->assigned_id = $assignTo;
                            $logs[] = $log;
                        }
                        $document->assign()->delete();
                        $document->assign()->saveMany($logs);
                    }
                }
                $document->load(['user', 'documentType', 'attachments']);
                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully updated the document.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to update the document.'], 400);

    }

    public function getDocuments (Request $request, $status = null) {
        $user = $request -> user();
        $allQuery = $request->query->all();
        $inStatus = 'ongoing,mydocument,releasing,done';

        if ($user->role->level > 1) {
            $inStatus = 'ongoing,done';
        }

        $validator = Validator::make(array_merge($allQuery, [
            'status' => $status
        ]), [
            'query' => 'present|nullable|string',
            'status' => "nullable|string|in:$inStatus"
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $searchQuery = $allQuery['query'];

        if ($user->role->level >= 2) {
            $documents = Document::whereHas('logs', function ($query) use ($user) {
                    $query->where('to_id', $user->id);
                })
                ->when($status === 'ongoing', function ($query) use ($user) {
                    $query->whereDoesntHave('logs', function ($query) {
                        $query->whereNotNull('released_at');
                    });
                })
                ->when($status === 'done', function ($query) use ($user) {
                    $query->whereHas('logs', function ($query) use ($user) {
                        $query->whereNotNull('released_at');
                    });
                })
                ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                        $query->whereHas('documentType', function ($query) use ($searchQuery) {
                        $query->where('description', 'like', "%$searchQuery%");
                    })
                    ->orWhereHas('sender', function ($query) use ($searchQuery) {
                            $query->whereHasMorph('receivable', [ChedOffice::class, Nga::class], function ($query) use ($searchQuery) {
                                $query->where('description', 'like', "%$searchQuery%");
                            })-> orWhereHasMorph('receivable', [Hei::class], function ($query) use ($searchQuery) {
                                $query->where('name', 'like', "%$searchQuery%");
                            })->orWhere('name', 'like', "%$searchQuery%");
                        })
                    ->orWhereHas('category', function ($query) use ($searchQuery) {
                        $query->where('description', 'like', "%$searchQuery%");
                    })
                    ->orWhere(function ($query) use ($searchQuery) {
                        $month = date('m', strtotime($searchQuery));
                        $query->whereYear('date_received', $searchQuery)
                            ->orWhereMonth('date_received', $month)
                            ->orWhereDay('date_received', $searchQuery);
                    });
                });
        })
        ->with(['attachments',
                'sender.receivable',
                'assign.assignedUser.profile',
                'logs.user.profile',
                'logs.acknowledgeUser.profile',
                'logs.actionUser.profile',
                'logs.approvedUser.profile',
                'logs.rejectedUser.profile',
                'logs.fromUser.profile',
                'logs.assignedUser.profile',
                'documentType',
                'category',
                'logs'=> function ($query){
                    $query -> orderBy('id', 'desc');
                }])
        ->orderBy('updated_at', 'desc')
        ->paginate(10);

        } else {
            $documents = Document::when($status === 'ongoing', function ($query) {
                $query->where(function ($query) {
                        $query->whereHas('assign', function ($query) {
                            $query->whereNotNull('assigned_id');
                        })
                        ->whereHas('logs', function ($query) {
                            $query->whereNotNull('to_id');
                        })
                        ->whereDoesntHave('logs', function ($query) {
                            $query->whereNotNull('released_at');
                        });
                    });
                })
                ->when($status === 'mydocument', function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->whereHas('logs', function ($query) use ($user) {
                                $query->where('to_id', $user->id);
                            })
                            ->orWhereHas('assign', function ($query) use ($user) {
                                $query->where('assigned_id', $user->id);
                            });
                    });
                })
                ->when($status === 'releasing', function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->whereHas('logs', function ($query) use ($user) {
                                $query->whereNull('to_id')->whereNotNull('from_id')
                                        ->whereNotNull('action_id')->whereNull('acknowledge_id')
                                            ->whereNotNull('approved_id')
                                                ->whereNull('released_at');
                            });
                    });
                })
                ->when($status === 'done', function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->whereHas('logs', function ($query) use ($user) {
                                $query->whereNotNull('released_at');
                            });
                    });
                })
                ->when($searchQuery, function ($query, $searchQuery) {
                    $query->where(function ($query) use ($searchQuery) {
                            $query->whereHas('documentType', function ($query) use ($searchQuery) {
                            $query->where('description', 'like', "%$searchQuery%");
                        })
                        ->orWhereHas('sender', function ($query) use ($searchQuery) {
                                $query->whereHasMorph('receivable', [ChedOffice::class, Nga::class], function ($query) use ($searchQuery) {
                                    $query->where('description', 'like', "%$searchQuery%");
                                })-> orWhereHasMorph('receivable', [Hei::class], function ($query) use ($searchQuery) {
                                    $query->where('name', 'like', "%$searchQuery%");
                                })->orWhere('name', 'like', "%$searchQuery%");
                            })
                        ->orWhereHas('category', function ($query) use ($searchQuery) {
                            $query->where('description', 'like', "%$searchQuery%");
                        })
                        ->orWhere(function ($query) use ($searchQuery) {
                            $month = date('m', strtotime($searchQuery));
                            $query->whereYear('date_received', $searchQuery)
                                ->orWhereMonth('date_received', $month)
                                ->orWhereDay('date_received', $searchQuery);
                        });
                    });
                })
                ->with(['attachments',
                        'sender.receivable',
                        'assign.assignedUser.profile',
                        'logs.user.profile',
                        'logs.acknowledgeUser.profile',
                        'logs.actionUser.profile',
                        'logs.approvedUser.profile',
                        'logs.rejectedUser.profile',
                        'logs.fromUser.profile',
                        'logs.assignedUser.profile',
                        'documentType',
                        'category',
                        'logs'=> function ($query){
                            $query -> orderBy('id', 'desc');
                        }])
                ->orderBy('updated_at', 'desc')
                ->paginate(5);
        }

        switch ($user->role->level) {
            case 1:
                $user = User::with([
                        'profile',
                        'role.division.role' => function ($query) {
                            $query->where('level', 3);
                        },
                        'role.division.role.user'
                    ])
                    ->get();
                break;
            case 2:
                    $user = User::with([
                        'profile',
                        'role.division.role' => function ($query) {
                            $query->where('level', 3);
                        },
                        'role.division.role.user'
                    ]) ->whereHas('role', function ($query) {
                        $query->where('level', '>', 2);
                    })
                    ->get();
                break;
            default:
                $minLevel = $user->role->level;
                $divisionId = $user->role->division_id;
                $user = User::with([
                        'profile',
                        'role.division.role' => function ($query) {
                            $query->where('level', 3);
                        },
                        'role.division.role.user'
                    ])
                    ->whereHas('role', function ($query) use ($minLevel, $divisionId) {
                        $query->where('level', '=', $minLevel +1)
                            ->where('level', '<>', 2)
                            ->where('division_id', $divisionId);
                    })
                    ->get();
                    break;
        }
             $documents->each(function($doc){
                 $doc->logs_grouped = $doc->logs->groupBy('assigned_id')->sortByDesc('id');
             });


        return response()->json([
            'data' => [
                'documents' => $documents,
                'user' => $user

            ],
            'message' => 'Successfully fetched the documents.'
        ], 200);
    }

    public function getDocument (Request $request, $id) {
        $user = $request->user();

        $document = Document::with([
            'attachments',
             'sender.receivable',
             'user.profile',
              'documentType',
               'category',
               'assign'=> function ($query){
                    $query -> orderBy('id', 'desc');
                },
                'assign.assignedUser.profile',
                'assign.assignedUser.role',
                'logs'=> function ($query){
                    $query -> orderBy('id', 'desc');
                },
                'logs.user.profile',
                'logs.acknowledgeUser.profile',
                'logs.actionUser.profile',
                'logs.approvedUser.profile',
                'logs.rejectedUser.profile',
                'logs.fromUser.profile',
                'logs.assignedUser.profile',
                'logs.assignedUser.role'
            ])
            ->when(!$user->role->level === 1, function($query) use ($user){
                $query->whereHas('logs', function ($query) use ($user) {
                    $query->where('to_id', $user->id);
                });
            })
            ->find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $document->logs_grouped = $document->logs->groupBy('assigned_id')->sortByDesc('id');

        $user->unreadNotifications()->where('data->document->id', $document->id)->get()->markAsRead();
        return response()->json(['data' => $document, 'message' => 'Successfully fetched the document.'], 200);
    }

    public function downloadDocumentFile (Request $request, $id, $fileName) {
        $document = Document::with('attachments')->has('attachments')->find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        if (Storage::disk('document_files')->missing($document->attachments->file_name)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return response()->file(Storage::disk('document_files')->path($document->attachments->file_name));
    }

    public function deleteDocument (Request $request, $id) {
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        try {
            $document->delete();
            $document->sender()->delete();
            Storage::disk('document_files')->deleteDirectory($document->id);

            return response()->json(['message' => 'Successfully deleted the document.'], 200);
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to delete the document.'], 400);

    }

    public function getDocumentSeries(Request $request, $documentTypeId){

        $validator = Validator::make([
            'document_type' => $documentTypeId
        ], [
            'document_type' => 'required|integer|exists:document_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $document = Document::where('document_type_id', $documentTypeId)->orderBy('series_no', 'DESC')->first();

        $seriesNo = $document ? $document->series_no+1 : 1;
        return response()->json(['data' => $seriesNo, 'message' => 'Successfully fetched the latest series number.'], 200);
    }

    public function getDocumentReceive (Request $request) {
        $user = $request->user();
        switch ($user->role->level) {
            case 1:
                $users = User::with([
                        'profile',
                        'role.division.role' => function ($query) {
                            $query->where('level', 3);
                        },
                        'role.division.role.user'
                    ])
                    ->whereHas('role', function ($query) {
                        $query->where(function ($query) {
                                $query->where('level', '<', 5)
                                    ->whereHas('division', function ($query) {
                                        $query->where('description', 'Administrative');
                                    });
                            })
                            ->orWhere(function ($query) {
                                $query->where('level', '<', 6)
                                    ->whereHas('division', function ($query) {
                                        $query->where('description', 'Technical');
                                    });
                            })
                            ->orWhere('level', 2);
                    })
                    ->where('id', '<>', $user->id)
                    ->get();
                break;
            default:
                $minLevel   = $user->role->level;
                $divisionId = $user->role->division_id;
                $users = User::with([
                        'profile',
                        'role.division.role' => function ($query) {
                            $query->where('level', 3);
                        },
                        'role.division.role.user'
                    ])
                    ->whereHas('role', function ($query) use ($minLevel, $divisionId) {
                        $query->where('level', '=', $minLevel +1)
                            ->where('level', '<>', 2)
                            ->where('division_id', $divisionId);
                    })
                    ->get();
                break;
        }

        $documentTypes = DocumentType::get();
        $categories = Category::get();


            return response()->json([
                'data' => [
                    'users' => $users,
                    'documentTypes' => $documentTypes,
                    'categories' => $categories,
                ],
                'message' => 'Successfully fetched the data.',
            ], 200);
    }

    public function forwardDocument (Request $request, $id) {
        $requestData = $request->only(['assign_to']);
        $user = $request->user();

        $validator = Validator::make($requestData, [
            'assign_to' => 'array|required|max:1',
            'assign_to.*' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $document = Document::with(['assign.assignedUser', 'logs', 'category'])->find($id);
        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $usersToAssign = User::with('role')->whereIn('id', $requestData['assign_to'])->get();

        if($usersToAssign->count() !== count($requestData['assign_to'])) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $usersAcknowledged = $document->logs->whereNotNull('acknowledge_id')->pluck('acknowledge_id');

        $divisions = Division::with([
            'role' => function ($query) {
                $query->where('level', 3);
            },
            'role.user'
        ])
        ->get();

        $director = User::whereHas('role', function ($query) {
            $query->where('level', 2);
        })
        ->first();

        $fromUser = User::whereHas('role', function ($query) {
            $query->where('level', 1);
        })
        ->first();

        try {
            DB::beginTransaction();

            if($document->category->is_assignable) {
                if ($document->logs()->whereNotNull('acknowledge_id')->exists()) {
                    $logs = [];

                    $acknowledgeLog = $document->logs()->where('acknowledge_id', $user->id)->orderBy('id', 'desc')->first();
                    foreach ($usersToAssign as $assignTo) {
                        $log        = new DocumentLog();
                        $log->assigned_id = $acknowledgeLog->assigned_id;
                        $log->from_id = $user->id;
                        $log->to_id = $assignTo->id;
                        $log->action_id = $acknowledgeLog->action_id;
                        $logs[]     = $log;
                    }

                    $document->logs()->saveMany($logs);
                    Notification::send([$assignTo, $fromUser, $user, $director], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $assignTo->profile->toArray()));

                } else {

                    $toRemove = collect([]);
                    foreach ($document->assign as $assigned) {
                        if (!$usersToAssign->where('id', $assigned->assigned_id)->first() && !$usersAcknowledged->search($assigned->assigned_id)) {
                            $assigned->delete();
                            $toRemove->push($assigned->assignedUser);
                        }
                    }

                    $assigned = [];
                    $logs = [];

                        if ($document->logs->count() === 0) {
                            $fromUser = User::whereHas('role', function ($query) {
                                $query->where('level', 1);
                            })
                            ->first();
                            $log = new DocumentLog();
                            $log->to_id = $director->id;
                            if(!$document->category->is_assignable) {
                                $log->assigned_id = $director->id;
                            }
                            $logs[] = $log;
                            Notification::send([$director, $fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $fromUser->profile->toArray(), $director->profile->toArray()));
                        }

                    foreach($divisions as $division) {

                        $chiefLogRow = $document->logs->where('to_id', $division->role->user->id)->where('from_id', $director->id)->first();

                        // Adding users
                        $filteredToAddUsers = $usersToAssign->filter(function ($value, int $key) use ($division) {
                            return $value !== null && $value->role->division_id === $division->id;
                        });


                        $subordinateLevel = $division->role->user->role->level+1;

                        $filteredLevel = $filteredToAddUsers->filter(function ($value) use ($subordinateLevel) {
                            return $value->role->level === $subordinateLevel;
                        });

                        $superior = $division->role->user;
                        $subordinate = User::whereHas('role', function ($query) use ($subordinateLevel, $division) {
                            $query->where('level', $subordinateLevel)->where('division_id', $division->id);
                        })->first();

                        if ($filteredToAddUsers->count() > 0) {
                            if (!$chiefLogRow) {
                                $log = new DocumentLog();
                                if ($filteredToAddUsers->where('id', $division->role->user->id)->first()) {
                                    $log->assigned_id = $division->role->user->id;
                                }
                                $log->to_id = $division->role->user->id;
                                $log->from_id = $director->id;
                                $logs[] = $log;
                                Notification::send([$fromUser, $director, $division->role->user], new DocumentForwarded($document->toArray(), $log->toArray(), $director->profile->toArray(), $division->role->user->profile->toArray()));
                            }

                            if ($filteredLevel->count() === 0) {

                                $filtered = $filteredToAddUsers->filter(function ($value) use ($subordinateLevel) {
                                    return $value->role->level > $subordinateLevel;
                                });

                                if ($filtered->count() > 0) {
                                    $log = new DocumentLog();
                                    $log->to_id = $subordinate->id;
                                    $log->from_id = $superior->id;
                                    $logs[] = $log;
                                    Notification::send([$fromUser, $director, $superior, $subordinate], new DocumentForwarded($document->toArray(), $log->toArray(), $superior->profile->toArray(), $subordinate->profile->toArray()));

                                    $superior = $subordinate->id;
                                }
                            }

                            foreach($filteredToAddUsers as $assignTo) {
                                if ($assignTo->id !== $superior->id) {
                                    if (!$document->assign->where('assigned_id', $assignTo->id)->first()) {
                                        $log = new DocumentLog();
                                        $log->assigned_id = $assignTo->id;
                                        $log->to_id = $assignTo->id;
                                        $log->from_id = $superior->id;
                                        $logs[] = $log;
                                        Notification::send([$fromUser, $director, $superior, $assignTo], new DocumentForwarded($document->toArray(), $log->toArray(), $superior->profile->toArray(), $assignTo->profile->toArray()));

                                        $documentAssignation = new DocumentAssignation();
                                        $documentAssignation->assigned_id = $assignTo->id;
                                        $assigned[] = $documentAssignation;
                                    } else if (!$document->logs->where('from_id', $superior->id)->where('to_id', $assignTo->id)->first()) {
                                        $log = new DocumentLog();
                                        $log->assigned_id = $assignTo->id;
                                        $log->to_id = $assignTo->id;
                                        $log->from_id = $superior->id;
                                        $logs[] = $log;
                                        Notification::send([$fromUser, $director, $superior, $assignTo], new DocumentForwarded($document->toArray(), $log->toArray(), $superior->profile->toArray(), $assignTo->profile->toArray()));
                                    }
                                } else if (!$document->assign->where('assigned_id', $assignTo->id)->first()) {
                                        $documentAssignation = new DocumentAssignation();
                                        $documentAssignation->assigned_id = $assignTo->id;
                                        $assigned[] = $documentAssignation;
                                }
                            }
                        }

                        $document->assign()->saveMany($assigned);
                        $document->logs()->saveMany($logs);

                        // Removing users
                        $filteredToRemoveUsers = $toRemove->filter(function ($value, int $key) use ($division) {
                            return $value !== null && $value->role->division_id === $division->id;
                        });

                        if ($filteredToRemoveUsers->count() > 0) {

                            $subordinateLevel = $division->role->user->role->level+1;

                            $filteredLevel = $filteredToRemoveUsers->filter(function ($value) use ($subordinateLevel) {
                                return $value->role->level === $subordinateLevel;
                            });

                            $superiorId = $division->role->user->id;

                            if ($filteredLevel->count() === 0) {
                                $subordinate = User::whereHas('role', function ($query) use ($subordinateLevel, $division) {
                                    $query->where('level', $subordinateLevel)->where('division_id', $division->id);
                                })->first();

                                $filtered = $filteredToRemoveUsers->filter(function ($value) use ($subordinateLevel) {
                                    return $value->role->level > $subordinateLevel;
                                });

                                if ($filtered->count() > 0) {
                                    $superiorId = $subordinate->id;
                                }
                            }

                            $removeIds = [];
                            foreach($filteredToRemoveUsers as $assignTo) {
                                if ($assignTo->id !== $superiorId) {
                                    $removeIds[] = $assignTo->id;
                                }
                            }

                            $document->logs()
                                ->whereIn('to_id', $removeIds)
                                ->where('from_id', $superiorId)
                                ->delete();

                            if ($superiorId !== $division->role->user->id) {
                                $subordinateLogRow = $document->logs->where('to_id', $superiorId)->where('from_id', $division->role->user->id)->first();
                                if ($subordinateLogRow && $document->logs()->where('from_id', $superiorId)->count() === 0) {
                                    $subordinateLogRow->delete();
                                }
                            }

                            if ($chiefLogRow && $document->logs()->where('from_id', $division->role->user->id)->count() === 0) {
                                $chiefLogRow->delete();
                            }
                        }
                    }
                }
            } else {

                if ($document->logs()->whereNotNull('acknowledge_id')->exists()) {
                    $logs = [];

                    $acknowledgeLog = $document->logs()->where('acknowledge_id', $user->id)->orderBy('id', 'desc')->first();
                    foreach ($usersToAssign as $assignTo) {
                        $log        = new DocumentLog();
                        $log->assigned_id = $acknowledgeLog->assigned_id;
                        $log->from_id = $user->id;
                        $log->to_id = $assignTo->id;
                        $log->action_id = $acknowledgeLog->action_id;
                        $logs[]     = $log;
                        Notification::send([$fromUser, $assignTo, $user], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $assignTo->profile->toArray()));
                    }

                    $document->logs()->saveMany($logs);
                } else {
                    $toRemove = collect([]);
                    foreach ($document->assign as $assigned) {
                        if (!$usersToAssign->where('id', $assigned->assigned_id)->first() && !$usersAcknowledged->search($assigned->assigned_id)) {
                            $assigned->delete();
                            $toRemove->push($assigned->assignedUser);
                        }
                    }

                    $document->logs()->where('from_id', $user->id)->whereIn('from_id', $toRemove->pluck('id')->toArray())->delete();

                    $logs = [];
                    $assigned = [];

                    foreach ($usersToAssign as $assignTo) {
                        $log        = new DocumentLog();
                        $log->assigned_id = $assignTo;
                        $log->from_id = $user->id;
                        $log->to_id = $assignTo->id;
                        $logs[]     = $log;

                        $documentAssignation = new DocumentAssignation();
                        $documentAssignation->assigned_id = $assignTo->id;
                        Notification::send([$assignTo, $user, $fromUser, $director], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $assignTo->profile->toArray()));
                    }

                    $document->logs()->saveMany($logs);
                    $document->assign()->saveMany($assigned);
                }
            }

            $document->load([
                'attachments',
                'sender.receivable',
                'user.profile',
                'assign.assignedUser.profile',
                'assign.assignedUser.role',
                'logs.user.profile',
                'logs.acknowledgeUser.profile',
                'logs.actionUser.profile',
                'logs.approvedUser.profile',
                'logs.rejectedUser.profile',
                'logs.fromUser.profile',
                'logs.assignedUser.profile',
                'logs.assignedUser.role',
                'documentType',
                'category',
                'logs'=> function ($query){
                    $query->orderBy('id', 'desc');
                },
                'assign'=> function ($query){
                    $query -> orderBy('id', 'desc');
                },
            ]);
            $document->logs_grouped = $document->logs->groupBy('assigned_id')->sortByDesc('id');

            DB::commit();
            return response()->json(['data' => $document, 'message' => 'Successfully forwarded the document.'], 201);

        } catch (\Exception $e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to forward document.'], 400);
    }

    public function acknowledgeDocument(Request $request, $id) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $document = Document::with('category')->find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $latest = $document->logs()->orderBy('id', 'desc')->first();

        if ($latest->acknowledge_id === $user->id || $latest->to_id !== $user->id) {
            return response()->json(['message' => 'You are not allowed to acknowledge this document.'], 401);
        }

        $recordsOfficer = User::whereHas('role', function ($query) {
            $query->where('level', 1);
        })
        ->first();

        $director = User::whereHas('role', function ($query) {
            $query->where('level', 2);
        })
        ->first();

        $logs = [];

        try {
            DB::beginTransaction();

            if(!$latest->from_id) {
                $fromUser = $recordsOfficer;

                $log = new DocumentLog();
                $log->assigned_id = $latest->assigned_id;
                $log->action_id = $latest->action_id;
                $log->acknowledge_id = $user->id;
                $logs[] = $log;

                Notification::send([$fromUser], new DocumentAcknowledged($document->toArray(), $log->toArray(), $user->profile->toArray()));
            } else {
                $fromUser = User::with('role')->find($latest->from_id);
                $users = $document->logs()->whereNotNull('to_id')->get()->pluck('to_id')->toArray();

                $users = User::whereIn('id', $users)->whereHas('role', function ($query) use ($fromUser) {
                    $query->where('level', '<', $fromUser->role->level);
                        })->where('id','<>', $user->id)->get();

                $log = new DocumentLog();
                $log->assigned_id = $latest->assigned_id;
                $log->action_id = $latest->action_id;
                $log->acknowledge_id = $user->id;
                $logs[] = $log;

                Notification::send($users->concat([$fromUser, $recordsOfficer]), new DocumentAcknowledged($document->toArray(), $log->toArray(), $user->profile->toArray()));
            }

            if ($document->logs()->saveMany($logs)) {
                $document->load([
                    'attachments',
                    'sender.receivable',
                    'user.profile',
                    'assign.assignedUser.profile',
                    'assign.assignedUser.role',
                    'logs.user.profile',
                    'logs.acknowledgeUser.profile',
                    'logs.actionUser.profile',
                    'logs.approvedUser.profile',
                    'logs.rejectedUser.profile',
                    'logs.fromUser.profile',
                    'logs.assignedUser.profile',
                    'logs.assignedUser.role',
                    'documentType',
                    'category',
                    'logs'=> function ($query){
                        $query->orderBy('id', 'desc');
                    },
                    'assign'=> function ($query){
                        $query -> orderBy('id', 'desc');
                    },
                ]);

                $document->logs_grouped = $document->logs->groupBy('assigned_id')->sortByDesc('id');


                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully acknowledged the document.'], 201);
            }

        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to acknowledge the document.'], 400);

    }

    public function actionDocument(Request $request, $id) {
        $requestData = $request->only(['comment']);

        $validator = Validator::make($requestData, [
            'comment' => 'required|string',
        ]);

         if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $document = Document::with('category')->find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $return = $document->logs()->where('to_id', $user->id)->orderBy('id', 'DESC')->first();
        $latest = $document->logs()->orderBy('id', 'desc')->first();
        if ($latest->acknowledge_id !== $user->id || $return->to_id !== $user->id) {
            return response()->json(['message' => 'Unable to take action on this document.'], 401);
        }


        $logs = [];

        try {
            DB::beginTransaction();

            $recordsOfficer = User::whereHas('role', function ($query) {
                $query->where('level', 1);
            })
            ->first();

            if(!$return->from_id) {
                $fromUser = $recordsOfficer;

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->action_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentActedOn($document->toArray(), $log->toArray(), $user->profile->toArray()));

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->from_id = $user->id;
                $log->to_id = $return->from_id;
                $log->action_id = $user->id;
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $fromUser->profile->toArray()));
            } else {
                $fromUser = User::with('role')->find($return->from_id);

                $users = $document->logs()->whereNotNull('to_id')->get()->pluck('to_id')->toArray();

                $users = User::whereIn('id', $users)->whereHas('role', function ($query) use ($fromUser) {
                    $query->where('level', '<', $fromUser->role->level);
                        })->get();

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->action_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send($users->concat([$fromUser, $recordsOfficer]), new DocumentActedOn($document->toArray(), $log->toArray(), $user->profile->toArray()));

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->from_id = $user->id;
                $log->to_id = $return->from_id;
                $log->action_id = $user->id;
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $fromUser->profile->toArray()));
                Notification::send($users->concat([$recordsOfficer]), new DocumentForwardedTo($document->toArray(), $log->toArray(), $fromUser->profile->toArray()));
            }


            if ($document->logs()->saveMany($logs)) {
                $document->load([
                    'attachments',
                    'sender.receivable',
                    'user.profile',
                    'assign.assignedUser.profile',
                    'assign.assignedUser.role',
                    'logs.user.profile',
                    'logs.acknowledgeUser.profile',
                    'logs.actionUser.profile',
                    'logs.approvedUser.profile',
                    'logs.rejectedUser.profile',
                    'logs.fromUser.profile',
                    'logs.assignedUser.profile',
                    'logs.assignedUser.role',
                    'documentType',
                    'category',
                    'logs'=> function ($query){
                        $query->orderBy('id', 'desc');
                    },
                    'assign'=> function ($query){
                        $query -> orderBy('id', 'desc');
                    },
                ]);
                $document->logs_grouped = $document->logs->groupBy('assigned_id')->sortByDesc('id');

                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully took action on the document.'], 201);
            }

        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to take action on the document.'], 400);
    }

    public function approveDocument(Request $request, $id) {
        $requestData = $request->only(['comment']);

        $validator = Validator::make($requestData, [
            'comment' => 'nullable|string',
        ]);

         if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $document = Document::with('category')->find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $latest = $document->logs()->orderBy('id', 'desc')->first();
        if ($latest->acknowledge_id !== $user->id) {
            return response()->json(['message' => 'Unable to approve this document.'], 401);
        }

        $action = $document->logs()->where('acknowledge_id', $user->id)
                                ->whereNotNull('action_id')->orderBy('id', 'desc')->first();
        $return = $document->logs()->where('to_id', $user->id)
                                ->whereNull('action_id')->first();

        $recordsOfficer = User::whereHas('role', function ($query) {
                                $query->where('level', 1);
                            })
                            ->first();

        try {
            DB::beginTransaction();
            $logs = [];

            if ($return->from_id) {
                $fromUser = User::with('role')->find($return->from_id);
                $actionUser = User::with('role')->find($action->action_id);

                $users = $document->logs()->whereNotNull('to_id')->get()->pluck('to_id')->toArray();

                $users = User::whereIn('id', $users)->whereHas('role', function ($query) use ($fromUser) {
                    $query->where('level', '<', $fromUser->role->level);
                        })->get();

                $log = new DocumentLog();
                $log->assigned_id = $action->assigned_id;
                $log->action_id = $action->action_id;
                $log->approved_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send($users->concat([$actionUser, $recordsOfficer]), new DocumentApproved($document->toArray(), $log->toArray(), $user->profile->toArray()));

                $log = new DocumentLog();
                $log->assigned_id = $action->assigned_id;
                $log->from_id = $user->id;
                $log->to_id = $return->from_id;
                $log->action_id = $action->action_id;
                $log->approved_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $fromUser->profile->toArray() ));
                Notification::send($users, new DocumentForwardedTo($document->toArray(), $log->toArray(), $fromUser->profile->toArray() ));
            } else {
                $fromUser = $recordsOfficer;

                $log = new DocumentLog();
                $log->assigned_id = $action->assigned_id;
                $log->action_id = $action->action_id;
                $log->approved_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentApproved($document->toArray(), $log->toArray(), $user->profile->toArray(), $fromUser->toArray()));

                $log = new DocumentLog();
                $log->assigned_id = $action->assigned_id;
                $log->from_id = $user->id;
                $log->to_id = $return->from_id;
                $log->action_id = $action->action_id;
                $log->approved_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $fromUser->profile->toArray() ));
            }

            $users = $document->logs()->whereNotNull('to_id')->get()->pluck('to_id')->toArray();

            $users = User::whereIn('id', $users)->get();

            $users = User::whereHas('role', function ($query) use ($fromUser) {
                $query->where('level', '>', $fromUser->role->level);
            })
            ->get();

            if ($document->logs()->saveMany($logs)) {
                $document->load([
                    'attachments',
                    'sender.receivable',
                    'user.profile',
                    'assign.assignedUser.profile',
                    'assign.assignedUser.role',
                    'logs.user.profile',
                    'logs.acknowledgeUser.profile',
                    'logs.actionUser.profile',
                    'logs.approvedUser.profile',
                    'logs.rejectedUser.profile',
                    'logs.fromUser.profile',
                    'logs.assignedUser.profile',
                    'logs.assignedUser.role',
                    'documentType',
                    'category',
                    'logs'=> function ($query){
                        $query->orderBy('id', 'desc');
                    },
                    'assign'=> function ($query){
                        $query -> orderBy('id', 'desc');
                    },
                ]);
                $document->logs_grouped = $document->logs->groupBy('assigned_id')->sortByDesc('id');

                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully approved the document.'], 201);
            }

        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to approve the document.'], 400);

    }

    public function rejectDocument(Request $request, $id) {
        $requestData = $request->only(['comment']);

        $validator = Validator::make($requestData, [
            'comment' => 'nullable|string',
        ]);

         if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $document = Document::with('category')->find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $latest = $document->logs()->orderBy('id', 'desc')->first();
        if ($latest->acknowledge_id !== $user->id) {
            return response()->json(['message' => 'Unable to reject this document.'], 401);
        }

        $action = $document->logs()->where('acknowledge_id', $user->id)
                                ->whereNotNull('action_id')->first();
        $return = $document->logs()->where('to_id', $user->id)
                                ->whereNotNull('action_id')->first();

        $recordsOfficer = User::whereHas('role', function ($query) {
                                $query->where('level', 1);
                            })
                            ->first();

        $logs   = [];

        try {
            DB::beginTransaction();

            if($return->from_id){
                $fromUser = User::with('role')->find($return->from_id);

                $users = $document->logs()->whereNotNull('to_id')->get()->pluck('to_id')->toArray();

                $users = User::whereIn('id', $users)->whereHas('role', function ($query) use ($fromUser) {
                    $query->where('level', '<', $fromUser->role->level);
                        })->where('id','<>', $user->id)->get();

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->action_id = $action->action_id;
                $log->rejected_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send($users->concat([$fromUser]), new DocumentRejected($document->toArray(), $log->toArray(), $user->profile->toArray()));

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->from_id = $user->id;
                $log->to_id = $return->from_id;
                $log->rejected_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $fromUser->profile->toArray()));
                Notification::send($users, new DocumentForwardedTo($document->toArray(), $log->toArray(), $fromUser->profile->toArray()));
            } else {
                $fromUser = $recordsOfficer;

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->action_id = $action->action_id;
                $log->rejected_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentRejected($document->toArray(), $log->toArray(), $user->profile->toArray()));

                $log = new DocumentLog();
                $log->assigned_id = $return->assigned_id;
                $log->from_id = $user->id;
                $log->to_id = $return->from_id;
                $log->rejected_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;
                Notification::send([$fromUser], new DocumentForwarded($document->toArray(), $log->toArray(), $user->profile->toArray(), $fromUser->profile->toArray()));
            }

            if ($document->logs()->saveMany($logs)) {
                $document->load([
                    'attachments',
                    'sender.receivable',
                    'user.profile',
                    'assign.assignedUser.profile',
                    'assign.assignedUser.role',
                    'logs.user.profile',
                    'logs.acknowledgeUser.profile',
                    'logs.actionUser.profile',
                    'logs.approvedUser.profile',
                    'logs.rejectedUser.profile',
                    'logs.fromUser.profile',
                    'logs.assignedUser.profile',
                    'logs.assignedUser.role',
                    'documentType',
                    'category',
                    'logs'=> function ($query){
                        $query->orderBy('id', 'desc');
                    },
                    'assign'=> function ($query){
                        $query -> orderBy('id', 'desc');
                    },
                ]);
                $document->logs_grouped = $document->logs->groupBy('assigned_id')->sortByDesc('id');

                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully rejected the document.'], 201);
            }

        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to approve the document.'], 400);

    }

    public function releaseDocument(Request $request, $id) {

        $requestData = $request->only(['date_released']);

        $validator = Validator::make($requestData, [
            'date_released' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $document = Document::with('category')->find($id);

        if (!$document) {
            return response()->json([
                'message' => 'Document not found.'
            ], 404);
        }

        $users = $document->logs()->whereNotNull('to_id')->get()->pluck('to_id')->toArray();

        $users = User::whereIn('id', $users)->get();

        $recordsOfficer = User::whereHas('role', function ($query) {
            $query->where('level', 1);
            })
            ->first();

        $director = User::whereHas('role', function ($query) {
            $query->where('level', 2);
            })
            ->first();

        $releasing = $document->logs()->where('from_id', $director->id)
                                    ->whereNull('to_id')
                                    ->first();

        if(!$releasing){
            return response()->json([
                'message' => 'Unable to release document.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $log = new DocumentLog();
            $log->assigned_id = $releasing->assigned_id;
            $log->action_id = $releasing->action_id;
            $log->released_at = Carbon::parse($requestData['date_released']);

            if ($document->logs()->save($log)) {
                Notification::send($users->concat([$recordsOfficer]), new DocumentReleased($document->toArray()));
                $document->load([
                    'attachments',
                    'sender.receivable',
                    'user.profile',
                    'assign.assignedUser.profile',
                    'assign.assignedUser.role',
                    'logs.user.profile',
                    'logs.acknowledgeUser.profile',
                    'logs.actionUser.profile',
                    'logs.approvedUser.profile',
                    'logs.rejectedUser.profile',
                    'logs.fromUser.profile',
                    'logs.assignedUser.profile',
                    'logs.assignedUser.role',
                    'documentType',
                    'category',
                    'logs'=> function ($query){
                        $query->orderBy('id', 'desc');
                    },
                    'assign'=> function ($query){
                        $query -> orderBy('id', 'desc');
                    },
                ]);
                $document->logs_grouped = $document->logs->groupBy('assigned_id')->sortByDesc('id');

                DB::commit();
                return response()->json(['data' => $document, 'message' => 'Successfully released the document.'], 201);
            }
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Failed to release the document.'
            ], 400);
        }
    }

    public function deleteAttachment(Request $request, $id) {
        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'message' => 'Document not found.'
            ], 404);
        }

        $attachment = Attachment::where('document_id', $document->id)->first();

        if (!$attachment) {
            return response()->json([
                'message' => 'Attachment not found.'
            ], 404);
        }

        try {
            $attachment->delete();
            Storage::disk('document_files')->deleteDirectory($document->id);

            return response()->json([
                'message' => 'Successfully deleted the Attachment.'
            ],200);

        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Failed to delete the attachment.'
            ], 400);

        }
    }
}
