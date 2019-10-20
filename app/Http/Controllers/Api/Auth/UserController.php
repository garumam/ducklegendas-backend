<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Support\Facades\Gate;
use App\Utils\Utils;

class UserController extends Controller
{
    //use VerifiesEmails;

    public $successStatus = 200;
    public $errorStatus = 403;

    public function login(Request $request){

        $validator = $this->validateLogin($request);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);        
        }
        if (!Auth::attempt($request->all())) {
            return response()->json(['error' => ['Erro ao logar, dados incorretos']], $this->errorStatus);
        }
        $user = Auth::user();

        //if($user->email_verified_at !== NULL){

            $user->tokens()->forcedelete();
            $tokenCreated = $user->createToken('Personal Access Token');
            $user['access_token'] = $tokenCreated->accessToken;
            $user['token_expirate'] = Carbon::parse($tokenCreated->token->expires_at)->format('Y-m-d H:i:s');
            return response()->json(['success' =>[
                'user' => $user,
            ]], $this->successStatus);

        //}

        //return response()->json(['error' => 'Por favor verifique seu e-mail!'], $this->errorStatus);
    }

    public function logout(){
        if (Auth::check()) {
            Auth::user()->token()->forcedelete();
            return response()->json(['success' =>['Logout efetuado com sucesso!']],$this->successStatus); 
        }else{
            return response()->json(['error' =>['Ocorreu um problema ao deslogar, atualize a página!']], $this->errorStatus);
        }
    }

    public function store(Request $request) 
    {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        } 

        $validator = $this->validateUser($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
         
        $input = $request->except('image');
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input); 
        
        if($user){
            Utils::update_image($user, $request, 'users');

            //$user->sendApiEmailVerificationNotification();
        
            //$success["message"] = "Please confirm yourself by clicking on verify user button sent to you on your email";
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    public function update(Request $request) 
    {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateUser($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $input = $request->except('image'); 
        $input['password'] = bcrypt($input['password']);
        $user = User::find($request->id);

        if($user){

            Utils::update_image($user, $request, 'users');
            $user->update($input);

            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }

        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);    
    }

    public function find($id){

        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $user = User::find($id);

        if($user){
            return response()->json(['success'=>$user], $this->successStatus);
        }else{
            return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);
        }
    }

    public function getAll(Request $request){

        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = User::where('id','like', '%'.$request->search.'%')
                    ->orWhere('name','like', '%'.$request->search.'%')
                    ->orWhere('email','like', '%'.$request->search.'%');
        $users = $query->paginate(100);

        return response()->json(['success'=>$users], $this->successStatus);
    }

    public function destroy($id) {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $user = User::find($id);

        if($user->delete()){
            return response()->json(['success'=>['Cadastro excluido com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    private function validateUser($request){
        return Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users,email,'.($request->id ? $request->id : ''), 
            'password' => 'required',
            'img' => 'nullable|image|mimes:jpeg,png,jpg|max:1000|dimensions:max_width=650,max_height=650'
        ]);
    }
    private function validateLogin($request){
        return Validator::make($request->all(), [ 
            'email' => 'required|email', 
            'password' => 'required|min:6',
        ]);
    }

}
