<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;
use Carbon\Carbon;
use Image;
use Illuminate\Foundation\Auth\VerifiesEmails;

class UserController extends Controller
{
    //use VerifiesEmails;

    public $successStatus = 200;
    public $errorStatus = 403;

    public function login(Request $request){

        
        if (!Auth::attempt($request->all())) {
            return response()->json(['error' => 'Erro no login ou senha'], $this->errorStatus);
        }
        $user = Auth::user();

        //if($user->email_verified_at !== NULL){

            $user->tokens()->forcedelete();
            $tokenCreated = $user->createToken('Personal Access Token');
            $expirateDate = Carbon::parse($tokenCreated->token->expires_at)->format('Y-m-d H:i:s');
            return response()->json([
                'user' => $user,
                'access_token' => $tokenCreated->accessToken,
                'token_expirate' => $expirateDate
            ], $this->successStatus);

        //}

        //return response()->json(['message' => 'Por favor verifique seu e-mail!'], $this->errorStatus);
    }

    public function logout(){
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            Auth::user()->token()->forcedelete();
            return response()->json(['success' =>'Logout efetuado com sucesso!'],$this->successStatus); 
        }else{
            return response()->json(['error' =>'Ocorreu um problema ao deslogar, atualize a página!'], $this->errorStatus);
        }
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

        $this->update_avatar($user, $request);

        //$user->sendApiEmailVerificationNotification();
      
        //$success["message"] = "Please confirm yourself by clicking on verify user button sent to you on your email";
        return response()->json(['success'=>'Cadastro efetuado com sucesso'], $this->successStatus); 
    }

    public function update_avatar($user ,Request $request) {
        
        $imageUri = '';

        if($request->hasFile('img')) {
           $avatar   = $request->file('img');
           $filename = $user->id . '.' . $avatar->getClientOriginalExtension();
           $imageUri = 'img/users/';
           $request->img->move($imageUri, $filename);
           $user->image = $imageUri . $filename;
           $user->save();
        }
        return $imageUri;
    }

    private function validateUser($request){
        return Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'password' => 'required',
            'img' => 'nullable|mimes:jpeg,jpg,png|max:1000'
        ]);
    }

}
