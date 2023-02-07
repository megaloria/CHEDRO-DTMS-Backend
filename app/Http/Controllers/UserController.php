<?php

namespace App\Http\Controllers;

use App\Models\profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    public function createUser(Request $request)
    {
        $requestData = $request->only(['username', 'password', 'prefix', 'first_name', 'middle_name', 'last_name', 'suffix', 'position_designation']);

        $validator = Validator::make($requestData, [
            'username'   => 'required|string|min:3',
            'password'   => 'required|min:8',
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'prefix'    => 'nullable|present|string',
            'suffix'    => 'nullable|present|string',
            'middle_name'    => 'nullable|present|string',
            'position_designation'    => 'required|string',


        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            $user = new User([

                'username' => $requestData['username'],
                'password' => Hash::make($requestData['password'])
            ]);

            if ($user->save()) {

                $profile = new Profile([
                    'prefix'               => $requestData['prefix'],
                    'first_name'           => $requestData['first_name'],
                    'middle_name'          => $requestData['middle_name'],
                    'last_name'            => $requestData['last_name'],
                    'suffix'               => $requestData['suffix'],
                    'position_designation' => $requestData['position_designation'],
                ]);

                return response()->json(['data' => [$user, 'profile' => $profile], 'message' => 'Successfully created a user'], 201);
            }
        } catch (\Exception$e) {
            report($e);
        }

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
        return response()->json(['message' => 'No user/s deleted.'], 400);

    }

}
