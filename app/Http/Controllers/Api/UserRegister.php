<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserRegister extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function store(Request $request) 
    {
        $validator = $this->validateUser($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
         
        $input = $request->except('image');
        $input['user_type'] = "legender";
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input); 
        
        if($user){
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    private function validateUser($request){
        return Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'password' => 'required'
        ]);
    }
}
