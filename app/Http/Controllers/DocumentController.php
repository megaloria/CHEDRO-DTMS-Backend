<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Document;
use App\Models\Attachment;


class DocumentController extends Controller 
{

    public function addDocument (Request $request) {
    
        $requestData = $request->only(['document_type_id', 'user_id', 'tracking_no', 'recieved_from', 'description', 'date_received','file_name','file_title']);

        $validator = Validator::make($requestData, [
        'document_type_id' => 'required|integer|exists:document_types,id',
        'user_id' => 'required|integer|exists:users,id',
        'tracking_no' => 'required|present|string',
        'recieved_from' => 'required|present|string',
        'description' => 'required|present|string',
        'date_received' => 'required|date',
        'file_name' => 'required|string',
        'file_title' => 'required|string',       

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();
    
            $document = new Document([
                'user_id'              => $requestData['user_id'],
                'document_type_id'     => $requestData['document_type_id'],
                'tracking_no'     => $requestData['tracking_no'],
                'recieved_from'     => $requestData['recieved_from'],
                'description'     => $requestData['description'],
                'date_received'     => $requestData['date_received'],
            ]);
 
            if ($document->save());
                $attachment = new Attachment([
                    'document_id' => $document->id,
                    'file_name'    => $requestData['file_name'],
                    'file_title'   => $requestData['file_title'],
                ]);

                $attachment->save();
                $document->load(['user','documentType','attachments']);
                DB::commit();

                return response()->json(['data' => $document, 'message' => 'Successfully added the document.'], 201);
            
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to add the document.'], 400);

    }


    public function editDocument (Request $request,$id) {

        $requestData = $request->only(['user_id', 'document_type_id','tracking_no', 'recieved_from','description', 'date_received']);
        $validator = Validator::make($requestData, [
            'user_id'        => 'required|integer|exists:users,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
            'tracking_no'        => 'required|string',
            'recieved_from' => 'required|string|min:3',
            'description'        => 'required|present|string',
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
            $document->tracking_no        = $requestData['tracking_no'];
            $document->recieved_from = $requestData['recieved_from'];
            $document->description        = $requestData['description'];
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
        $document = document::with  ('attachments')->get();


        return response()->json(['data' => $document,  'message' => 'Successfully fetched the documents.'], 200);
    }

    
    public function getDocument (Request $request, $id) {
        $document = document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document Type not found.'], 404);
        }

        return response()->json(['data' => $document, 'message' => 'Successfully fetched the document.'], 200);
        
    }

    public function deleteDocument (Request $request, $id) {
    $document = Document::find($id);

    if (!$document) {
        return response()->json(['message' => 'Role not found.'], 404);
    }

    try {
        $document->delete();

        return response()->json(['message' => 'Successfully deleted the document.'], 200);

    } catch (\Exception $e) {
        report($e);
    }

    return response()->json(['message' => 'Failed to delete the document.'], 400);

}

}
