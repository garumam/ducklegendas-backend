<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserController extends Controller
{
    public function login(Request $request){
        if (!Auth::attempt($request->all())) {
            return response()->json(['message' => 'Erro no login ou senha'], 402);
        }
        $user = Auth::user();
        $user->tokens()->forcedelete();
        $accessToken = $user->createToken('Personal Access Token')->accessToken;
        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function register(Request $request) 
    { 
        $validator = $this->validateUser($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input); 
        $success['access_token'] =  $user->createToken('Personal Access Token')->accessToken; 
        $success['name'] =  $user->name;
        $success['token_type'] =  'Bearer';
        return response()->json(['success'=>$success], 200); 
    }

    private function validateUser($request){
        return Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'password' => 'required'
        ]);
    }

}
