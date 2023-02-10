<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Role;

class RoleController extends Controller
{
    public function addRole(Request $request)
    {
        $requestData = $request->only(['description']);

        $validator = Validator::make($requestData, [
            'description'   => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try 
        {
            DB::beginTransaction();

            $role = new Role([
                'description' => $requestData['description'],
            ]);

            if ($role->save()) {


                DB::commit();
                return response()->json(['data' => $role, 'message' => 'Successfully created a role.'], 201);

            }

        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to create a role.'], 400);
    }

    public function getRoles (Request $request) {

        $roles = Role::get();
        
        return response()->json(['data' => $roles, 'message' => 'Successfully fetched the roles.'], 200);
    }

    public function getRole (Request $request, $id) {

        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        return response()->json(['data' => $role, 'message' => 'Successfully fetched the role.'], 200);
        
    }

    public function editRole (Request $request, $id) {

        $requestData = $request->only(['description']);

        $validator = Validator::make($requestData, [
            'description'   => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $role = Role::find($id);
    
        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }    

        try {
            $role->description = $requestData['description'];

            if ($role->save()) {
                return response()->json(['data' => $role, 'message' => 'Successfully updated the role.'], 201);
            }
        } catch (\Exception $e) {
            report($e);
        }
        
        return response()->json(['message' => 'Failed to update the role'], 400);
    }

    public function deleteRole (Request $request, $id) {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        try {
            $role->delete();

            return response()->json(['message' => 'Successfully deleted the role.'], 200);

        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to update the role.'], 400);

    }
}
