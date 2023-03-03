<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Hei;

class HEISController extends Controller
{
    public function addHEI (Request $request) {
        $requestData = $request->only(['uii','name','head_of_institution','street_barangay', 'city_municipality', 'province', 'email']);

        $validator = Validator::make($requestData, [
            'uii' => 'required|string',
            'name' => 'required|string|min:5',
            'head_of_institution' => 'required|string|min:5',
            'street_barangay' => 'required|string|min:5',
            'city_municipality' => 'required|string|min:5',
            'province' => 'required|string|min:5',
            'email' => 'required|email',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();

            $hei = new Hei([
                'uii' => $requestData['uii'],
                'name' => $requestData['name'],
                'head_of_institution' => $requestData['head_of_institution'],
                'street_barangay' => $requestData['street_barangay'],
                'city_municipality' => $requestData['city_municipality'],
                'province' => $requestData['province'],
                'email' => $requestData['email'],
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
        $requestData = $request->only(['uii','name', 'head_of_institution','street_barangay', 'city_municipality', 'province', 'email']);

       $validator = Validator::make($requestData, [
            'uii' => 'required|string',
            'name' => 'required|string|min:5',
            'head_of_institution' => 'required|string|min:5',
            'street_barangay' => 'required|string|min:5',
            'city_municipality' => 'required|string|min:5',
            'province' => 'required|string|min:5',
            'email' => 'required|email',

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
            $hei->head_of_institution = $requestData['head_of_institution'];
            $hei->street_barangay = $requestData['street_barangay'];
            $hei->city_municipality = $requestData['city_municipality'];
            $hei->province = $requestData['province'];
            $hei->email = $requestData['email'];


            if ($hei->save()) {
                return response()->json(['data' => $hei, 'message' => 'Successfully updated the HEI'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the HEI.'], 400);
    }


    public function getHEIS (Request $request) {
        $heiS = Hei::paginate(6);

        return response()->json(['data' => $heiS, 'message' => 'Successfully fetched the HEIS.'], 200);
    }


    public function getHEI (Request $request, $id) {
        $hei = Hei::find($id);

        if (!$hei) {
            return response()->json(['message' => 'HEI not found.'], 404);
        }

        return response()->json(['data' => $hei, 'message' => 'Successfully fetched the HEI.'], 200);
    }

    public function getProvinces()
    {
        $provinces = HEI::select('province')->distinct()->orderBy('province')->get();

        return response()->json(['data' => $provinces, 'message' => 'Successfully fetched the provinces.'], 200);
    }

    public function getMunicipalities()
    {
        $municipalities = HEI::select('city_municipality')->distinct()->orderBy('city_municipality')->get();

        return response()->json(['data' => $municipalities, 'message' => 'Successfully fetched the municipalities.'], 200);
    }
    public function getNames()
    {
        $names = HEI::select('name')->distinct()->orderBy('name')->get();

        return response()->json(['data' => $names, 'message' => 'Successfully fetched the names.'], 200);
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
