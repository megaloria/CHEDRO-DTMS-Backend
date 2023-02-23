<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\ChedOffice;

class ChedOfficesController extends Controller
{
    public function addChedOffice (Request $request) {
        $requestData = $request->only(['code','description','email']);

        $validator = Validator::make($requestData, [
            'code' => 'required|integer',
            'description' => 'required|string|min:5',
            'email' => 'required|email'
           
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();

            $ched = new ChedOffice([
                'code' => $requestData['code'],
                'description' => $requestData['description'],
                'email' => $requestData['email'],
             
            ]);

            if ($ched->save()) {
                DB::commit();
                return response()->json(['data' => $ched, 'message' => 'Successfully added a new Ched Office.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to add the Ched Office.'], 400);
    }

    public function editChedOffice (Request $request,$id) {
        $requestData = $request->only(['code','description','email']);

        $validator = Validator::make($requestData, [
            'code' => 'required|integer',
            'description' => 'required|string|min:5',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $ched = ChedOffice::find($id);

        if (!$ched) {
            return response()->json(['message' => 'Ched Office not found.'], 404);
        }

        try {
            $ched->code = $requestData['code'];
            $ched->description = $requestData['description'];
            $ched->email = $requestData['email'];
         

            if ($ched->save()) {
                return response()->json(['data' => $ched, 'message' => 'Successfully updated the Ched Office'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the Ched Office.'], 400);
    }

    public function getChedOffices (Request $request) {
        $ched = ChedOffice::get();

        return response()->json(['data' => $ched, 'message' => 'Successfully fetched the Ched Offices.'], 200);
    }

    public function getChedOffice (Request $request, $id) {
        $ched = ChedOffice::find($id);

        if (!$ched) {
            return response()->json(['message' => 'Ched Office not found.'], 404);
        }

        return response()->json(['data' => $ched, 'message' => 'Successfully fetched the Ched Office.'], 200);
    }

    public function deleteChedOffice (Request $request, $id) {
        $ched = ChedOffice::find($id);

        if (!$ched) {
            return response()->json(['message' => 'Ched Office not found.'], 404);
        }

        try {
            $ched->delete();

            return response()->json(['message' => 'Successfully deleted the Ched Office.'], 200);
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to delete the Ched Office.'], 400);
    }
}
