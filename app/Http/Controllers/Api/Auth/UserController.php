<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\VerifiesEmails;

class UserController extends Controller
{
    //use VerifiesEmails;

    public $successStatus = 200;
    public $errorStatus = 401;

    public function login(Request $request){

        
        if (!Auth::attempt($request->all())) {
            return response()->json(['message' => 'Erro no login ou senha'], $this->errorStatus);
        }
        $user = Auth::user();

        //if($user->email_verified_at !== NULL){

            $user->tokens()->forcedelete();
            $tokenCreated = $user->createToken('Personal Access Token');
            $expirateDate = Carbon::parse($tokenCreated->token->expires_at)->format('Y-m-d H:i:s');
            return response()->json([
                'user' => $user,
                'access_token' => $tokenCreated->accessToken,
                'token_type' => 'Bearer',
                'token_expirate' => $expirateDate
            ], $this->successStatus);

        //}

        //return response()->json(['message' => 'Por favor verifique seu e-mail!'], $this->errorStatus);
    }

    public function register(Request $request) 
    { 
        $validator = $this->validateUser($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input); 
        //$user->sendApiEmailVerificationNotification();
        $tokenCreated = $user->createToken('Personal Access Token');
        $success['access_token'] =  $tokenCreated->accessToken; 
        $success['name'] =  $user->name;
        $success['token_expirate'] = Carbon::parse($tokenCreated->token->expires_at)->format('Y-m-d H:i:s');
        $success['token_type'] =  'Bearer';
        //$success["message"] = "Please confirm yourself by clicking on verify user button sent to you on your email";
        return response()->json(['success'=>$success], $this->successStatus); 
    }

    private function validateUser($request){
        return Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'password' => 'required'
        ]);
    }

}
