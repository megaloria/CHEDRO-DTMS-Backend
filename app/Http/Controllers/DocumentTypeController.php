<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\DocumentType;


class DocumentTypeController extends Controller
{
    public function addDocumentType (Request $request) {
        $requestData = $request->only(['code','description','days']);

        $validator = Validator::make($requestData, [
            'code' => 'required|string|min:4',
            'description' => 'required|min:3',
            'days' => 'required|integer|min:1',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();

            $documents = new DocumentType([
                'code' => $requestData['code'],
                'description' => $requestData['description'],
                'days' => $requestData['days']
            ]);

            if ($documents->save()) {
                DB::commit();
                return response()->json(['data' => $documents, 'message' => 'Successfully created a document type.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to create a document type.'], 400);
    }



    public function editDocumentType (Request $request,$id) {
        $requestData = $request->only(['code','description', 'days']);

        $validator = Validator::make($requestData, [
            'code' => 'required|string|min:4',
            'description' => 'required|string|min:3',
            'days' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $documents = DocumentType::find($id);

        if (!$documents) {
            return response()->json(['message' => 'Document type not found.'], 404);
        }

        try {
            $documents->code = $requestData['code'];
            $documents->description = $requestData['description'];
            $documents->days = $requestData['days'];

            if ($documents->save()) {
                return response()->json(['data' => $documents, 'message' => 'Successfully updated the document type.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the document type.'], 400);
    }


    public function getDocumentTypesPaginate (Request $request) {
        $documents = DocumentType::paginate(6);

        return response()->json(['data' => $documents, 'message' => 'Successfully fetched the document types.'], 200);
    }

    public function getDocumentTypes (Request $request) {
        $documents = DocumentType::get();

        return response()->json(['data' => $documents, 'message' => 'Successfully fetched the document types.'], 200);
    }


    public function getDocumentType (Request $request, $id) {
        $documents = DocumentType::find($id);

        if (!$documents) {
            return response()->json(['message' => 'Document type not found.'], 404);
        }

        return response()->json(['data' => $documents, 'message' => 'Successfully fetched the document type.'], 200);
    }

    //closing tag///

    public function deleteDocumentType (Request $request, $id) {
        $documents = DocumentType::find($id);

        if (!$documents) {
            return response()->json(['message' => 'Document type not found.'], 404);
        }

        try {
            $documents->delete();

            return response()->json(['message' => 'Successfully deleted the document type.'], 200);
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to delete the document type.'], 400);
    }

}
    


















































