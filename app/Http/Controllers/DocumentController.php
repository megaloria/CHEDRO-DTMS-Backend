<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Document;
use App\Models\User;
use App\Models\DocumentType;


class DocumentController extends Controller
{
    public function addDocumentRequest(Request $request) {
        // $user = $request->user();

        $requestData = $request->only(['document_type_id', 'user_id', 'tracking_no', 'recieved_from', 'description', 'date_received']);

        $validator = Validator::make($requestData, [
        'document_type_id' => 'required|integer|exists:document_types, id',
        'user_id' => 'required|integer|exists:users, id',
        'tracking_no' => 'required|present|string',
        'recieved_from' => 'required|present|string',
        'description' => 'required|present|string',
        'date_received' => 'required|date',

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
 
            $document->save();

            $document->load('users');

            DB::commit();
            return response()->json(['data' => $document, 'message' => 'Successfully'], 201);
            
            } catch (\Exception$e) {
            report($e);
            }

            DB::rollBack();
            return response()->json(['message' => 'Failed'], 400);

    }
}
