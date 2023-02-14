<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Division;

class DivisionController extends Controller
{
    public function addDivision(Request $request){

        $requestData = $request->only(['description']);

        $validator = Validator::make($requestData, [
        'description' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }
        try 
            {
            DB::beginTransaction();
            $division = new Division([
            'description' => $requestData['description'],
            ]);

            if ($division->save()) {

            DB::commit();
            return response()->json(['data' => $division, 'message' => 'Successfully created a Division.'], 201);
            }

            } catch (\Exception$e) {
                report($e);
            }

            DB::rollBack();
            return response()->json(['message' => 'Failed to create a Division.'], 400);
        }

//closing//

    public function editDivision(Request $request, $id){

        $requestData = $request->only(['description']);

        $validator = Validator::make($requestData, [
            'description'   => 'required|string|min:3',
            
        ]);

        if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $division = Division::find($id);

        if (!$division) {
        return response()->json(['message' => 'Division not found.'], 404);
        }

        try {
            $division->description = $requestData['description'];

            if ($division->save()) {
                return response()->json(['data' => $division, 'message' => 'Successfully updated the Divistion.'], 201);
            }
        } catch (\Exception $e) {
            report($e);
        }
        
        return response()->json(['message' => 'Failed to update the Division'], 400);
    }

//closing//

    public function deleteDivision (Request $request, $id) {
            
        $division = Division::find($id);

        if (!$division) {
            return response()->json(['message' => 'Division not found.'], 404);
        }

        try {
            $division->delete();

            return response()->json(['message' => 'Successfully deleted the Division.'], 200);

        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the Division.'], 400);

    }
}



