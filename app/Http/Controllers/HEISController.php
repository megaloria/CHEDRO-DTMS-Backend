<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Hei;

class HEISController extends Controller
{
    public function addHEI (Request $request) {
        $requestData = $request->only(['uii','name','head_of_institution','address']);

        $validator = Validator::make($requestData, [
            'uii' => 'required|integer',
            'name' => 'required|string|min:5',
            'address' => 'required|string',
            'head_of_institution' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();

            $hei = new Hei([
                'uii' => $requestData['uii'],
                'name' => $requestData['name'],
                'address' => $requestData['address'],
                'head_of_institution' => $requestData['head_of_institution']
            ]);

            if ($hei->save()) {
                DB::commit();
                return response()->json(['data' => $hei, 'message' => 'Successfully added a new HEI.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();

        return response()->json(['message' => 'Failed to add the HEI.'], 400);
    }



    public function editHEI (Request $request,$id) {
        $requestData = $request->only(['uii','name', 'head_of_institution','address']);

        $validator = Validator::make($requestData, [
            'uii' => 'required|integer',
            'name' => 'required|string|min:5',
            'address' => 'required|string',
            'head_of_institution' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $hei = Hei::find($id);

        if (!$hei) {
            return response()->json(['message' => 'HEI not found.'], 404);
        }

        try {
            $hei->uii = $requestData['uii'];
            $hei->name = $requestData['name'];
            $hei->address = $requestData['address'];
            $hei->head_of_institution = $requestData['head_of_institution'];

            if ($hei->save()) {
                return response()->json(['data' => $hei, 'message' => 'Successfully updated the HEI'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the HEI.'], 400);
    }


    public function getHEIS (Request $request) {
        $heiS = Hei::get();

        return response()->json(['data' => $heiS, 'message' => 'Successfully fetched the HEIS.'], 200);
    }


    public function getHEI (Request $request, $id) {
        $hei = Hei::find($id);

        if (!$hei) {
            return response()->json(['message' => 'HEI not found.'], 404);
        }

        return response()->json(['data' => $documents, 'message' => 'Successfully fetched the HEI.'], 200);
    }

    //closing tag///

    public function deleteHEI (Request $request, $id) {
        $hei = Hei::find($id);

        if (!$hei) {
            return response()->json(['message' => 'HEI not found.'], 404);
        }

        try {
            $hei->delete();

            return response()->json(['message' => 'Successfully deleted the HEI.'], 200);
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to delete the HEI.'], 400);
    }
}
