<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Nga;

class NGASController extends Controller
{
    public function addNGA (Request $request) {
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

            $nga = new Nga([
                'code' => $requestData['code'],
                'description' => $requestData['description'],
                'email' => $requestData['email'],
             
            ]);

            if ($nga->save()) {
                DB::commit();
                return response()->json(['data' => $nga, 'message' => 'Successfully added a new NGA.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to add the NGA.'], 400);
    }

    public function editNGA (Request $request,$id) {
        $requestData = $request->only(['code','description','email']);

        $validator = Validator::make($requestData, [
            'code' => 'required|integer',
            'description' => 'required|string|min:5',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $nga = Nga::find($id);

        if (!$nga) {
            return response()->json(['message' => 'NGA not found.'], 404);
        }

        try {
            $nga->code = $requestData['code'];
            $nga->description = $requestData['description'];
            $nga->email = $requestData['email'];
         

            if ($hei->save()) {
                return response()->json(['data' => $nga, 'message' => 'Successfully updated the NGA'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the NGA.'], 400);
    }

    public function getNGAS (Request $request) {
        $nga = Nga::get();

        return response()->json(['data' => $nga, 'message' => 'Successfully fetched the NGAs.'], 200);
    }

    public function getNGA (Request $request, $id) {
        $nga = Nga::find($id);

        if (!$nga) {
            return response()->json(['message' => 'NGA not found.'], 404);
        }

        return response()->json(['data' => $nga, 'message' => 'Successfully fetched the NGA.'], 200);
    }

    public function deleteNGA (Request $request, $id) {
        $nga = Nga::find($id);

        if (!$nga) {
            return response()->json(['message' => 'NGA not found.'], 404);
        }

        try {
            $nga->delete();

            return response()->json(['message' => 'Successfully deleted the NGA.'], 200);
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to delete the NGA.'], 400);
    }

}
