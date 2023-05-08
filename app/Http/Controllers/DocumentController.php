<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
            'assign_to' => 'array|nullable',
            'assign_to.*' => 'integer|min:1|exists:users,id'
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
                    $fileUrl = Storage::url($fileName);
                }

                if (!$category->is_assignable) {
                    $assignTo = Profile::where(function ($query) {
                            $query->where('position_designation', 'like', 'Regional Director%');
                    })->value('id');
                    $log      = new DocumentAssignation([
                        'assigned_id' => $assignTo,
                    ]);
                    $document->assign()->save($log);
                } else if (array_key_exists('assign_to', $requestData) && $requestData['assign_to']) {
                    $logs = [];
                    foreach ($requestData['assign_to'] as $assignTo) {
                        $logs[] = new DocumentAssignation([
                            'assigned_id' => $assignTo,
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
            'assign_to' => 'array|nullable',
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

         if (array_key_exists('assign_to', $requestData) && $requestData['assign_to']) {
            $users = User::with('role')->whereIn('id', $requestData['assign_to'])->get();
            if ($users->count() !== count($requestData['assign_to'])) {
                return response()->json(['message' => 'User not found.'], 404);
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
                    $assignTo = Profile::where(function ($query) {
                            $query->where('position_designation', 'like', 'Regional Director%');
                    })->value('id');
                    $assign      = new DocumentAssignation([
                        'assigned_id' => $assignTo,
                    ]);

                    $log      = new DocumentLog([
                        'to_id' => $assignTo,
                    ]);
                    $document->assign()->save($assign);
                    $document->logs()->save($log);

                } else if (array_key_exists('assign_to', $requestData) && $requestData['assign_to']) {
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

                    $log = new DocumentLog();
                    $log->to_id = Profile::where(function ($query) {
                            $query->where('position_designation', 'like', 'Regional Director%');
                    })->value('id');
                    $logs[] = $log;

                    foreach($divisions as $division) {
                        $filteredUsers = $users->filter(function ($value, int $key) use($division) {
                            return $value->role->division_id === $division->id;
                        });

                        if($filteredUsers->count() > 0){
                            $log = new DocumentLog();

                            $log->to_id = $division->role->user->id;
                            $log->from_id = Profile::where(function ($query) {
                                    $query->where('position_designation', 'like', 'Regional Director%');
                            })->value('id');
                            $logs[] = $log;

                            $subordinateLevel = $division->role->user->role->level+1;

                            $filteredLevel = $filteredUsers->filter(function ($value) use ($subordinateLevel) {
                                return $value->role->level === $subordinateLevel;
                            });

                            $superiorId = $division->role->user->id;

                            if ($filteredLevel->count() === 0) {
                                $subordinate = User::whereHas('role', function ($query) use ($subordinateLevel) {
                                    $query->where('level', $subordinateLevel);
                                })->first();

                                $log = new DocumentLog();
                                $log->to_id = $subordinate->id;
                                $log->from_id = $superiorId;
                                $logs[] = $log;

                                $superiorId = $subordinate->id;
                            }

                            foreach($filteredUsers as $assignTo) {
                                if ($assignTo->id !== $superiorId) {
                                    $log = new DocumentLog();
                                    $log->assigned_id = $assignTo->id;
                                    $log->to_id = $assignTo->id;
                                    $log->from_id = $superiorId;
                                    $logs[] = $log;
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
            'assign_to' => 'array|nullable',
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
                    $assignTo = Profile::where(function ($query) {
                            $query->where('position_designation', 'like', 'Regional Director%');
                    })->value('id');

                    $log      = new DocumentAssignation([
                        'assigned_id' => $assignTo,
                    ]);
                    $document->assign()->save($log);
                } else if (array_key_exists('assign_to', $requestData) && $requestData['assign_to']) {
                    if ($category -> is_assignable) {
                        $logs = [];
                        foreach($requestData['assign_to'] as $assignTo) {
                            $log = new DocumentAssignation();
                            $log->assigned_id = $assignTo;
                            $logs[] = $log;
                        }
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

        $validator = Validator::make(array_merge($allQuery, [
            'status' => $status
        ]), [
            'query' => 'present|nullable|string',
            'status' => 'nullable|string|in:ongoing,mydocument,releasing,done'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $searchQuery = $allQuery['query'];

        if ($user->role->level >= 2) {
            $documents = Document::whereHas('logs', function ($query) use ($user) {
                $query->where('to_id', $user->id);
            })->when($searchQuery, function ($query, $searchQuery) {
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
                'documentType', 
                'category',
                'logs'=> function ($query){
                    $query -> orderBy('id', 'desc');
                }])
        ->orderBy('updated_at', 'desc')
        ->paginate(5);

        } else {
            $documents = Document::when($status === 'ongoing', function ($query) {
            $query->where(function ($query) {
                $query->whereHas('assign', function ($query) {
                    $query->whereNotNull('assigned_id');
                })->whereHas('logs', function ($query) {
                    $query->whereNotNull('to_id');
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
                    ->whereHas('role', function ($query) {
                        $query->where('level', '<>', 2);
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
                'logs'=> function ($query){
                    $query -> orderBy('id', 'desc');
                },
                 'logs.user.profile',
                  'logs.acknowledgeUser.profile'])
            ->when(!$user->role->level === 1, function($query) use ($user){
                $query -> whereHas('logs', function ($query) use ($user) {
                $query->where('to_id', $user->id);
            });
            })
            ->find($id);

           $fileUrl = Storage::url($document->attachments?->file_name);


        if (!$document) {
            return response()->json(['message' => 'Document Type not found.'], 404);
        }

        return response()->json(['data' => $document, 'url' => $fileUrl, 'message' => 'Successfully fetched the document.'], 200);

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
                        $query->where('level', '<>', 2);
                    })
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
            'assign_to' => 'array|required|min:1',
            'assign_to.*' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $document = Document::with(['assign.assignedUser', 'logs'])->find($id);
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

        $director = Profile::where(function ($query) {
                $query->where('position_designation', 'like', 'Regional Director%');
            })
            ->first();

        try {
            DB::beginTransaction();

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
                $log = new DocumentLog();
                $log->to_id = $director->id;
                $logs[] = $log;
            }

            foreach($divisions as $division) {

                $chiefLogRow = $document->logs->where('to_id', $division->role->user->id)->where('from_id', $director->id)->first();

                // Adding users
                $filteredToAddUsers = $usersToAssign->filter(function ($value, int $key) use ($division) {
                    return $value->role->division_id === $division->id;
                });

                if ($filteredToAddUsers->count() > 0) {
                    if (!$chiefLogRow) {
                        $log = new DocumentLog();
                        $log->to_id = $division->role->user->id;
                        $log->from_id = $director->id;
                        $logs[] = $log;
                    }

                    $subordinateLevel = $division->role->user->role->level+1;

                    $filteredLevel = $filteredToAddUsers->filter(function ($value) use ($subordinateLevel) {
                        return $value->role->level === $subordinateLevel;
                    });

                    $superiorId = $division->role->user->id;

                    if ($filteredLevel->count() === 0) {
                        $subordinate = User::whereHas('role', function ($query) use ($subordinateLevel) {
                            $query->where('level', $subordinateLevel);
                        })->first();

                        $log = new DocumentLog();
                        $log->to_id = $subordinate->id;
                        $log->from_id = $superiorId;
                        $logs[] = $log;

                        $superiorId = $subordinate->id;
                    }

                    foreach($filteredToAddUsers as $assignTo) {
                        if ($assignTo->id !== $superiorId) {
                            if (!$document->assign->where('assigned_id', $assignTo->id)->first()) {
                                $log = new DocumentLog();
                                $log->to_id = $assignTo->id;
                                $log->from_id = $superiorId;
                                $logs[] = $log;

                                $documentAssignation = new DocumentAssignation();
                                $documentAssignation->assigned_id = $assignTo->id;
                                $assigned[] = $documentAssignation;
                            } else if (!$document->logs->where('from_id', $superiorId)->where('to_id', $assignTo->id)->first()) {
                                $log = new DocumentLog();
                                $log->to_id = $assignTo->id;
                                $log->from_id = $superiorId;
                                $logs[] = $log;
                            }
                        } else {
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
                    return $value->role->division_id === $division->id;
                });

                if ($filteredToRemoveUsers->count() > 0) {
                    $removeIds = [];
                    foreach($filteredToRemoveUsers as $assignTo) {
                        if ($assignTo->id !== $division->role->user->id) {
                            $removeIds[] = $assignTo->id;
                        }
                    }

                    $document->logs()
                        ->whereIn('to_id', $removeIds)
                        ->where('from_id', $division->role->user->id)
                        ->delete();

                    if ($chiefLogRow && $document->logs()->where('from_id', $division->role->user->id)->count() === 0) {
                        $chiefLogRow->delete();
                    }
                }
            }

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

        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }
        $return = $document->logs()->where('to_id', $user->id)->orderBy('id','desc')->first();
        $actioned = $document->logs()->where('action_id', $return->from_id)->exists();
        $logs = [];


        try {
            DB::beginTransaction();

            if ($actioned) {
                $log = new DocumentLog();
                $log->action_id = $return->from_id;
                $log->acknowledge_id = $user->id;
                $logs[] = $log;
            } else {
                $log = new DocumentLog();
                $log->acknowledge_id = $user->id;
                $logs[] = $log;
            }

            if ($document->logs()->saveMany($logs)) {
                DB::commit();
                return response()->json(['data' => $log, 'message' => 'Successfully acknowledged the document.'], 201);
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

        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }
        $return = $document->logs()->where('to_id', $user->id)->first();
        $logs = [];

        try {
            DB::beginTransaction();

            $log = new DocumentLog();
            $log->action_id = $user->id;
            $log->comment = $requestData['comment'];
            $logs[] = $log;

            $log = new DocumentLog();
            $log->from_id = $user->id;
            $log->to_id = $return->from_id;
            $log->action_id = $user->id;
            $logs[] = $log;


            if ($document->logs()->saveMany($logs)) {
                DB::commit();
                return response()->json(['data' => $log, 'message' => 'Successfully took action on the document.'], 201);
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

        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }
        $action = $document->logs()->where('acknowledge_id', $user->id)
                                ->whereNotNull('action_id')->first();
        $return = $document->logs()->where('to_id', $user->id)
                                ->whereNull('action_id')->first();
        $logs = [];

        try {
            DB::beginTransaction();

                $log = new DocumentLog();
                $log->action_id = $action->action_id;
                $log->approved_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;

                $log = new DocumentLog();
                $log->from_id = $user->id;
                $log->to_id = $return->from_id;
                $log->action_id = $action->action_id;
                $log->approved_id = $user->id;
                $log->comment = $requestData['comment'];
                $logs[] = $log;

            if ($document->logs()->saveMany($logs)) {
                DB::commit();
                return response()->json(['data' => $log, 'message' => 'Successfully approved the document.'], 201);
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

        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }
        $action = $document->logs()->where('acknowledge_id', $user->id)
                                ->whereNotNull('action_id')->first();
        $return = $document->logs()->where('to_id', $user->id)
                                ->whereNotNull('action_id')->first();
        $logs   = [];

        try {
            DB::beginTransaction();

            $log = new DocumentLog();
            $log->action_id = $action->action_id;
            $log->rejected_id = $user->id;
            $log->comment = $requestData['comment'];
            $logs[] = $log;

            $log = new DocumentLog();
            $log->from_id = $user->id;
            $log->to_id = $return->from_id;
            $log->action_id = $action->action_id;
            $log->rejected_id = $user->id;
            $log->comment = $requestData['comment'];
            $logs[] = $log;

            if ($document->logs()->saveMany($logs)) {
                DB::commit();
                return response()->json(['data' => $log, 'message' => 'Successfully rejected the document.'], 201);
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

        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'message' => 'Document not found.'
            ], 404);
        }

        $director = Profile::where(function ($query) {
                $query->where('position_designation', 'like', 'Regional Director%');
            })
            ->first();

        $release = $document->logs()->where('from_id', $director->id)
                                    ->whereNull('to_id')
                                    ->whereNotNull('approved_id')
                                    ->first();

        try {
            DB::beginTransaction();

            $release->released_at = Carbon::parse($requestData['date_released']);

            if ($release->save()) {
                DB::commit();
                return response()->json(['data' => $release, 'message' => 'Successfully released the document.'], 201);
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
