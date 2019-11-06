<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{

    public $errorStatus = 404;
    public $successStatus = 200;

    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function create(Request $request)
    {
       
        $validator = $this->validateCreate($request);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], $this->errorStatus); 
        }
        $user = User::where('email', $request->email)->first();
        if (!$user)
            return response()->json([
                'error' => ["Não encontramos um usuário com este e-mail."]
            ], $this->errorStatus);
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60)
             ]
        );
        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequest($passwordReset->token, $request->urlFront)
            );
        return response()->json([
            'success' => ['Enviamos o link para troca de senha para seu e-mail!']
        ], $this->successStatus);
    }
    
     /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {
        
        $validator = $this->validateReset($request);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], $this->errorStatus); 
        }
        
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();
        
        $error = $this->validationToken($passwordReset);
        
        if (empty($error)){

            $user = User::where('email', $passwordReset->email)->first();
            if (!$user)
                return response()->json([
                    'error' => ["Não encontramos um usuário com este e-mail."]
                ], $this->errorStatus);
            $user->password = bcrypt($request->password);
            $user->save();
            $passwordReset->delete();
            $user->notify(new PasswordResetSuccess());
            return response()->json(['success' => ['Senha alterada com sucesso!']], $this->successStatus);

        }
        
        return response()->json(['error' => $error], $this->errorStatus);
        
    }

    public function validationToken($passwordReset)
    {
        if (!$passwordReset)
            return ['Este token para troca de senha é inválido.'];
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return ['Este token para troca de senha é inválido.'];
        } 
        return '';
    }

    private function validateCreate($request){
        return Validator::make($request->all(), [ 
            'email' => 'required|string|email',
            'urlFront' => 'required|string'
        ]);
    }

    private function validateReset($request){
        return Validator::make($request->all(), [ 
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);
    }
}
