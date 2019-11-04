<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Message;

class MessageController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function list(){
        $messages = Message::where('status', 'ON')->get();
        return response()->json(['success'=>$messages], $this->successStatus);
    }

    public function getAll(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = Message::where('id','like', '%'.$request->search.'%')
                        ->orWhere('message','like', '%'.$request->search.'%')
                        ->orWhere('type','like', '%'.$request->search.'%')
                        ->orWhere('status','like', '%'.$request->search.'%');

        $messages = $query->paginate(100);

        return response()->json(['success'=>$messages], $this->successStatus);
    }

    public function find($id){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $message = Message::find($id);

        if($message){
            return response()->json(['success'=>$message], $this->successStatus);
        }else{
            return response()->json(['error'=>['Mensagem não encontrada']], $this->errorStatus);
        }
    }

    public function store(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }
       
        $validator = $this->validateMessage($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }

        $message = Message::create($request->all()); 
        
        if($message){
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    public function update(Request $request) {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateMessage($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $message = Message::find($request->id);

        if($message){
            $message->update($request->all());
            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Mensagem não encontrada']], $this->errorStatus);   
    }

    public function destroy($id) {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $message = Message::find($id);

        if($message->delete()){
            return response()->json(['success'=>['Cadastro excluido com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Mensagem não encontrada']], $this->errorStatus);   
    }

    private function validateMessage($request){
        return Validator::make($request->all(), [ 
            'message' => 'required',
            'type' => [
                'required',
                Rule::in(['primary','success', 'danger','warning'])
            ],
            'status' => [
                'required',
                Rule::in(['ON', 'OFF'])
            ]
        ]);
    }
}
