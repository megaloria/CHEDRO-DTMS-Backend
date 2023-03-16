<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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



class DocumentController extends Controller 
{
    public function addDocument (Request $request) {
        $user = $request->user();

        $requestData = $request->only(['document_type_id', 'date_received', 'receivable_type', 'receivable_id', 'receivable_name', 'description', 'category_id']);
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
            'category_id' => 'required|integer|exists:categories,id'
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
            } else {
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


    public function editDocument (Request $request,$id) {
        $requestData = $request->only(['user_id', 'document_type_id','tracking_no', 'recieved_from','description', 'date_received']);
        $validator = Validator::make($requestData, [
            'user_id' => 'required|integer|exists:users,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
            'tracking_no' => 'required|string',
            'recieved_from' => 'required|integer|min:3',
            'description' => 'required|present|string',
            'date_received' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        try {
            $document->user_id = $requestData['user_id'];
            $document->document_type_id = $requestData['document_type_id'];
            $document->tracking_no = $requestData['tracking_no'];
            $document->sender_id = $requestData['sender_id'];
            $document->description = $requestData['description'];
            $document->date_received = $requestData['date_received']; 
          
            if ($document->save()) {
                return response()->json(['data' => $document, 'message' => 'Successfully updated the document.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }
        
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
                $query->where('description', 'like', "%$searchQuery%");
            })
            ->orWhereHas('category', function ($query) use ($searchQuery) {
                $query->where('description', 'like', "%$searchQuery%");
            });
        })->with(['attachments','sender.receivable'])->paginate(5);

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
        $document = Document::with(['attachments','sender.receivable','user.profile','documentType','category'])->find($id);
        
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

    
    

}
