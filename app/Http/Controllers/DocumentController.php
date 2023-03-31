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
use App\Models\DocumentLog;
use App\Models\DocumentAssignation;



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

                if (array_key_exists('assign_to', $requestData) && $requestData['assign_to']) {
                    $logs = [];
                    foreach($requestData['assign_to'] as $assignTo) {
                        $logs[] = new DocumentAssignation([
                            'assigned_id' => $assignTo,
                        ]);
                    }
                    $document->assign()->saveMany($logs);
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
            'category_id' => 'required|integer|exists:categories,id',
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

               $document->assign()->delete();

                 if (array_key_exists('assign_to', $requestData) && $requestData['assign_to']) {
                    if ($requestData['category_id'] == 3) {
                        unset($requestData['assign_to']);
                    } else {
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
    
    public function getDocuments (Request $request) {
        $allQuery = $request->query->all();

        $validator = Validator::make($allQuery, [
            'query' => 'present|nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $searchQuery = $allQuery['query'];

        $documents = Document::when($searchQuery, function ($query, $searchQuery) {
            $query->whereHas('documentType', function ($query) use ($searchQuery) {
                $query->where('description', 'like', "%$searchQuery%");
            })
            ->orWhereHas('sender', function ($query) use ($searchQuery) {
                $query->whereHasMorph('receivable', [ChedOffice::class, Nga::class, Hei::class], function ($query) use ($searchQuery) {
                    $query->where('description', 'like', "%$searchQuery%");
                });
            })
            ->orWhereHas('category', function ($query) use ($searchQuery) {
                $query->where('description', 'like', "%$searchQuery%");
            })
            ->orWhere(function ($query) use ($searchQuery) {
                $date = date('Y-m-d', strtotime($searchQuery));
                $month = date('m', strtotime($searchQuery));
                $query->whereYear('date_received', $searchQuery)
                    ->orWhereMonth('date_received', $month)
                    ->orWhereDay('date_received', $searchQuery);
            });
        })
        ->with(['attachments', 'sender.receivable', 'assign.assignedUser.profile', 'logs.user.profile'])
        ->paginate(5);
        
        $documentType = DocumentType::get();
        $category = Category::get();
        $user = User::with(['profile'])->get();

        return response()->json([
            'data' => [
                'documents' => $documents,
                'documentType' => $documentType,
                'category' => $category,
                'user' => $user
            ],
            'message' => 'Successfully fetched the documents.'
        ], 200);
    }
    
    public function getDocument (Request $request, $id) {
        $document = Document::with(['attachments', 'sender.receivable', 'user.profile', 'documentType', 'category', 'assign.assignedUser.profile', 'logs'])->find($id);
     
        if (!$document) {
            return response()->json(['message' => 'Document Type not found.'], 404);
        }

        return response()->json(['data' => $document, 'message' => 'Successfully fetched the document.'], 200);

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
        $users = User::with(['profile'])->get();
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
    $requestData = $request->only(['assign_to' ]);
   
    $validator = Validator::make($requestData, [
        'assign_to' => 'array|required',
        'assign_to.*' => 'integer|min:1|exists:users,id'
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 409);
    }

    $document = Document::find($id);
    if (!$document) {
        return response()->json(['message' => 'Document not found.'], 404);
    }

    $document->assign()->delete();

    if (array_key_exists('assign_to', $requestData) && $requestData['assign_to']) {
        $logs = [];
        foreach($requestData['assign_to'] as $assignTo) {
            $log = new DocumentAssignation();
            $log->assigned_id = $assignTo;
            $logs[] = $log;
        }

        foreach($requestData['assign_to'] as $assignTo) {
            $log = new DocumentLog();
            $log->to_id = $assignTo;
            $logs[] = $log;
        }

    $document->assign()->saveMany($logs);
    $document->logs()->saveMany($logs);

    }
    return response()->json(['data' => $document, 'message' => 'Successfully forwarded the document.'], 201);

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
