<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\profile;
class UserController extends Controller
{
    
    public function createUser (Request $request) {
        $requestData = $request->only(['username', 'password','prefix','first_name','middle_name','last_name','suffix','position_designation']);

        $validator = Validator::make($requestData, [
            'username' => 'required|string|min:3',
            'password' => 'required',
            'prefix' => '',
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'suffix' => '',
            'position_designation' => ''

        ]);

        
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            $user = new User([
                
                'username' =>  $requestData['username'],
                'password' => $requestData['password']
            ]);

            if ($user->save()) {
                $profile = new Profile([
                    'prefix' => $requestData['prefix'],
                    'first_name' => $requestData['first_name'],
                    'middle_name' => $requestData['middle_name'],
                    'last_name' => $requestData['last_name'],
                    'suffix' => $requestData['suffix'],
                    'position_designation' => $requestData['position_designation']                 
                ]);

                return response()->json(['data' => $user,$profile, 'message' => 'Successfully created a user'], 201);
            }
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to create a user'], 400);

    }





}
