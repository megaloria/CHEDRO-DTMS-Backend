<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Profile;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller 
{
    public function createUser (Request $request) {
        $requestData = $request->only(['username', 'password', 'prefix', 'first_name', 'middle_name', 'last_name', 'suffix', 'role_id', 'division_id', 'level', 'description','position_designation']);

        $validator = Validator::make($requestData, [
            'username' => 'required|string|min:3',
            'password' => 'required|min:8',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'prefix' => 'nullable|present|string',
            'suffix' => 'nullable|present|string',
            'middle_name' => 'nullable|present|string',
            'role_id' => 'required|integer|exists:roles,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'level' => 'required|integer',
            'description' => 'required|string',
            'position_designation' => 'nullable|present|string'
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
                    'prefix' => $requestData['prefix'],
                    'first_name' => $requestData['first_name'],
                    'middle_name' => $requestData['middle_name'],
                    'last_name' => $requestData['last_name'],
                    'suffix' => $requestData['suffix'],
                    'position_designation' => $requestData['position_designation'],
                ]);

                $profile->save();

                $user->load(['profile','role']);
                DB::commit();
                return response()->json(['data' => $user, 'message' => 'Successfully created a user.'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to create a user.'], 400);
    }

    public function deleteUser (Request $Request, $id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            $user->delete();

            return response()->json(['message' => 'Successfully deleted the user.'], 200);
        } catch (\Exception$e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to delete the user.'], 400);
    }

    public function getUsers (Request $request) {
        $user = User::paginate(10);

        return response()->json(['data' => $user, 'message' => 'Successfully fetched the users.'], 200);
    }

    public function getUser (Request $request, $id) {
        $user = User::find($id);
        
        return response()->json(['data' => $user, 'message' => ' Successfully fetched the user.'], 200);
    }

    public function editUser (Request $request,$id) {
        $requestData = $request->only(['prefix','first_name','middle_name','last_name','suffix', 'position_designation']);

        $validator = Validator::make($requestData, [
            'prefix' => 'nullable|present|string|min:2',
            'first_name' => '|string|min:3',
            'middle_name' => 'nullable|string|min:3',
            'last_name' => '|string|min:3',
            'suffix' => 'nullable|string|min:2',
            'position_designation' => 'required|string',
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
                return response()->json(['data' => $editUser, 'message' => 'Successfully updated the user.'], 201);
            }
        } catch (\Exception $e) {
            report($e);
        }
    
        return response()->json(['message' => 'Failed to update the user'], 400);
    }

    public function login(Request $request) {
        $requestData = $request->only(['username', 'password']);
        
        $validator = Validator::make($requestData, [
            'username' => 'required|min:5',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid credentials!'], 409);
        }

        $user = User::where('username', $requestData['username'])->first();

        if (!$user || !Hash::check($requestData['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials!'], 409);
        }

        try {
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($requestData['password']);
                $user->save();
            }

            Auth::guard('web')->login($user);
            $user->load('profile');
            return response()->json(['message' => 'Successfully logged in.', 'data' => $user], 200);
        } catch (\Exception $e) {
            report($e);
        }
        
        return response()->json(['message' => 'Failed to login.'], 400);
    }

    public function getCurrentUser (Request $request) {
        $user = $request->user();
        $user->load('profile');
        return response()->json(['data' => $user, 'message' => 'Successfully fetched current user.'], 200);
    }

    public function logout (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Successfully logged out.'], 200);
    }
}

