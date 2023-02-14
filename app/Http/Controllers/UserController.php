<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
// use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;



class UserController extends Controller
{

    public function createUser(Request $request)
    {
        $requestData = $request->only(['username', 'password', 'prefix', 'first_name', 'middle_name', 'last_name', 'suffix', 'position_designation', 'role_id']);

        $validator = Validator::make($requestData, [
            'username'   => 'required|string|min:3',
            'password'   => 'required|min:8',
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'prefix'    => 'nullable|present|string',
            'suffix'    => 'nullable|present|string',
            'middle_name'    => 'nullable|present|string',
            'position_designation'    => 'required|string',
            'role_id' => 'required|integer|exists:roles,id'

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();

            $user = new User([

                'username' => $requestData['username'],
                'password' => Hash::make($requestData['password']),
                'role_id' => $requestData['role_id']
            ]);

            if ($user->save()) {

                $profile = new Profile([
                    'id' => $user->id,
                    'prefix'               => $requestData['prefix'],
                    'first_name'           => $requestData['first_name'],
                    'middle_name'          => $requestData['middle_name'],
                    'last_name'            => $requestData['last_name'],
                    'suffix'               => $requestData['suffix'],
                    'position_designation' => $requestData['position_designation'],
                ]);

                $profile->save();
                $user->load('profile');

                DB::commit();
                return response()->json(['data' => $user, 'message' => 'Successfully created a user'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to create a user'], 400);

    }

    public function deleteUser(Request $Request, $id)
    {
        try {
            $user = User::find($id);

            if ($user) {
                $user->delete();

                return response()->json(['message' => 'Deleted Successfully'], 200);
            }
        } catch (\Exception$e) {
            report($e);
        }
        return response()->json(['message' => 'No user deleted.'], 400);

    }

    public function getUser(Request $request, $id) {

        $users = User::paginate(10);
        
        return response()->json(['data' => $users, 'message' => ' Successfully'], 200);

        // $user = User::with([
        //     'profile',
        //     'role'
        // ])->find($id);
        
        // return response()->json(['data' => $user, 'message' => ' Successfully'], 200);

    }

    //closing tag//

    public function editUser (Request $request,$id){

            $requestData = $request->only(['prefix','first_name','middle_name','last_name','suffix','position_designation']);
            $validator = Validator::make($requestData, [

                'prefix' => 'nullable|present|string|min:2',
                'first_name' => '|string|min:3',
                'middle_name' => 'nullable|string|min:3',
                'last_name' => '|string|min:3',
                'suffix' => 'nullable|string|min:2',
                'position_designation' => '|string|min:3'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 409);
            }

            $editUser = Profile::find($id);

            if (!$editUser) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            try {
                $editUser->prefix = $requestData['prefix'];
                $editUser->first_name = $requestData['first_name'];
                $editUser->middle_name = $requestData['middle_name'];
                $editUser->last_name = $requestData['last_name'];
                $editUser->suffix = $requestData['suffix'];
                $editUser->position_designation = $requestData['position_designation'];

            if ($editUser->save()) {
                return response()->json(['data' => $editUser, 'message' => 'Successfully updated the User.'], 201);
            }
        } catch (\Exception $e) {
            report($e);
        }
        
        return response()->json(['message' => 'Failed to update the User'], 400);
    }

    //closing tag//


     public function getUsers(Request $request){
        $user = User::get();

        return response()->json(['data' => $user, 'message' => 'Successfully fetched the users.'], 200);

    }


  }

