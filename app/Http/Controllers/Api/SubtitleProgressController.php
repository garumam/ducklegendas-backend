<?php

namespace App\Http\Controllers\Api;

use App\SubtitleProgress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SubtitleProgressController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function list(Request $request){
        $subtitles = SubtitleProgress::orderBy('updated_at', 'desc')->get();

        return response()->json(['success'=>$subtitles], $this->successStatus);
    }

    public function getAll(Request $request){
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
            || Gate::allows('isAutor')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = SubtitleProgress::where('id','like', '%'.$request->search.'%')
                    ->orWhere('name','like', '%'.$request->search.'%')
                    ->orWhere('percent','like', '%'.$request->search.'%');
                    
        $subtitles = $query->paginate(100);

        return response()->json(['success'=>$subtitles], $this->successStatus);
    }

    public function find($id){

        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
            || Gate::allows('isAutor')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $subtitle = SubtitleProgress::find($id);
       
        if($subtitle){
            return response()->json(['success'=>$subtitle], $this->successStatus);
        }else{
            return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);
        }
    }

    public function store(Request $request){
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
            || Gate::allows('isAutor')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }
        
        $validator = $this->validateSubtitleProgress($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        $input = $request->all();

        $input['author'] = $request->user()->id;
        $subtitle = SubtitleProgress::create($input); 

        if($subtitle){
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    public function update(Request $request) 
    {
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
            || Gate::allows('isAutor')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateSubtitleProgress($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $subtitle = SubtitleProgress::find($request->id);

        if($subtitle){

            $subtitle->update($request->all());

            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }

        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    public function destroy($id) {
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
            || Gate::allows('isAutor')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $subtitle = SubtitleProgress::find($id);

        if($subtitle->delete()){
            return response()->json(['success'=>['Cadastro excluido com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    private function validateSubtitleProgress($request){
        return Validator::make($request->all(), [ 
            'name' => 'required|string', 
            'percent' => 'required|integer',
            'author' => 'nullable', 
        ]);
    }
}
