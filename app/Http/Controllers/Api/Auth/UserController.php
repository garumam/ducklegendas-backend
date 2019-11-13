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
use Illuminate\Support\Facades\Storage;

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
        if(!Gate::any(['isAdmin','isModerador'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        } 

        $validator = $this->validateUser($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
         
        $input = $request->except('image');

        if(Gate::allows('isModerador')){
            if(in_array($input['user_type'], ['admin','moderador'])){
                return response()->json(['error'=> ['Você não pode criar usuários moderadores ou admins!']], $this->errorStatus);
            }
        }

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
        if(!Gate::any(['isAdmin','isModerador','isAutor','isLegender'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        if(!$request->hasFile('image')){
            $request->merge(['image' => null]);
        }

        $validator = $this->validateUser($request,true);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        $input = null;
        if(!empty($request->password)){
            $input = $request->except('image');
            $input['password'] = bcrypt($input['password']);
        }else{
            $input = $request->except('image','password');
        } 
        
        $user = User::find($request->id);

        if($user){

            if(Gate::allows('isModerador')){
                $arrayOfTypes = ['admin','moderador'];
                if($request->user()->id !== $user->id){
                    if(in_array($user->user_type, $arrayOfTypes)){
                        return response()->json(['error'=> ['Você não pode editar usuários moderadores ou admins!']], $this->errorStatus);
                    }
                    if(in_array($input['user_type'], $arrayOfTypes)){
                        return response()->json(['error'=> ['Você não pode criar usuários moderadores ou admins!']], $this->errorStatus);
                    }
                }else{
                    if($input['user_type'] !== $request->user()->user_type){
                        return response()->json(['error'=> ['Você não pode mudar seu tipo de usuário!']], $this->errorStatus);
                    }
                }
            }

            if(Gate::any(['isAutor','isLegender'])){
                if($request->user()->id !== $user->id){
                    return response()->json(['error'=> ['Você não pode editar outros usuários!']], $this->errorStatus);
                }else{
                    if($input['user_type'] !== $request->user()->user_type){
                        return response()->json(['error'=> ['Você não pode mudar seu tipo de usuário!']], $this->errorStatus);
                    }
                }
            }

            if($request->hasFile('image')){
                Storage::delete($user->image);
                Utils::update_image($user, $request, 'users');
            }
            
            $user->update($input);

            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }

        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);    
    }

    public function find(Request $request, $id){

        if(!Gate::any(['isAdmin','isModerador','isAutor','isLegender'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $user = User::find($id);

        if($user){

            if(Gate::any(['isAutor','isLegender'])){
                if($request->user()->id !== $user->id){
                    return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
                }
            }

            return response()->json(['success'=>$user], $this->successStatus);
        }else{
            return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);
        }
    }

    public function getAll(Request $request){

        if(!Gate::any(['isAdmin','isModerador'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = User::where('id','like', '%'.$request->search.'%')
                    ->orWhere('name','like', '%'.$request->search.'%')
                    ->orWhere('email','like', '%'.$request->search.'%');
        $users = $query->paginate(100);

        return response()->json(['success'=>$users], $this->successStatus);
    }

    public function destroy($id) {
        if(!Gate::any(['isAdmin','isModerador'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $user = User::find($id);

        if($user){

            if(Gate::allows('isModerador')){
                $arrayOfTypes = ['admin','moderador'];
                if(in_array($user->user_type, $arrayOfTypes)){
                    return response()->json(['error'=> ['Você não pode deletar usuários moderadores ou admins!']], $this->errorStatus);
                }
            }

            $path = $user->image;
            if(Storage::delete($path) || !Storage::exists($path) || $user->image === null){
                $user->delete();
                return response()->json(['success'=>['Cadastro excluido com sucesso']], $this->successStatus);
            }
            return response()->json(['error'=>['Não foi possível deletar o usuário']], $this->errorStatus);
        }
        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    private function validateUser($request, $update = false){
        return Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users,email,'.($request->id ? $request->id : ''), 
            'password' => $update?'nullable':'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1000|dimensions:max_width=650,max_height=650'
        ]);
    }
    private function validateLogin($request){
        return Validator::make($request->all(), [ 
            'email' => 'required|email', 
            'password' => 'required|min:6',
        ]);
    }

}
