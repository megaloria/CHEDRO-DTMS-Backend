<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Documents;


class DocumentController extends Controller
{
    public function addDocumentType(Request $request){

        $requestData = $request->only(['code','description']);

        $validator = Validator::make($requestData, [
        'code'             => 'required|string|min:4',
        'description'             => 'required|min:3',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();

            $documents = new Documents([

                'code' => $requestData['code'],
                'description' => $requestData['description']
            ]);

            if ($documents->save()) {

            DB::commit();
            return response()->json(['data' => $documents, 'message' => 'Successfully created a document.'], 201);

            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to create a document.'], 400);
    }

//closing tag///

    public function editDocumentType(Request $request,$id){
        $requestData = $request->only(['code','description']);
        $validator = Validator::make($requestData, [
        'code' => 'required|string|min:4',
        'description' => 'required|string|min:3'
        ]);

        if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $documents = Documents::find($id);

        if (!$documents) {
        return response()->json(['message' => 'Document Type not found.'], 404);
        }

        try {
        $documents->code = $requestData['code'];
        $documents->description = $requestData['description'];

        if ($documents->save()) {
        return response()->json(['data' => $documents, 'message' => 'Successfully updated the document type.'], 201);
        }
        } catch (\Exception$e) {
        report($e);
        }

        return response()->json(['message' => 'Failed to update the document type'], 400);

    }

//closing tag///

    public function getDocumentTypes(Request $request){
        $documents = Documents::get();

        return response()->json(['data' => $documents, 'message' => 'Successfully fetched the document types.'], 200);

    }

//closing tag///

    public function getDocumentType(Request $request, $id){
        $documents = Documents::find($id);

        if (!$documents) {
            return response()->json(['message' => 'Document Type not found.'], 404);
        }

        return response()->json(['data' => $documents, 'message' => 'Successfully fetched the document type.'], 200);
        
    }

    //closing tag///

     public function deleteDocumentType (Request $request, $id) {
        $documents = Documents::find($id);

        if (!$documents) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        try {
            $documents->delete();

            return response()->json(['message' => 'Successfully deleted the document type.'], 200);

        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the document type.'], 400);

    }

    }
    


















































