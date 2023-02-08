<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class RoleController extends Controller
{
    public function createRole(Request $request)
    {
        $requestData = $request->only(['position_designation']);

        $validator = Validator::make($requestData, [
            'position_designation'   => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            $role = new Role([

                'position_designation' => $requestData['position_designation'],
            ]);
            if ($role->save()){

                return response()->json(['message' => 'Successfully added a role.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to add a role.'], 400);

    }
}
