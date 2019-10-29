<?php

namespace App\Http\Controllers\Api;

use App\Subtitle;
use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Utils\Utils;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SubtitleController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function list(Request $request){

        $query = Subtitle::where('status','=','APROVADA')->where(function($q) use ($request){
            $q->where('name','like', '%'.$request->search.'%')
                    ->orWhere('year','like', '%'.$request->search.'%')
                    ->orWhere('category','like', '%'.$request->search.'%');
        });
        $today = Carbon::now();

        switch($request->order){
            case "hoje":
                $firstDate = $today->toDateString();
                break;
            case "semana":
                $firstDate = $today->subDays(7)->toDateString();
                break;
            case "mes":
                $firstDate = $today->subDays(30)->toDateString();
                break;
            case "semestre":
                $firstDate = $today->subDays(182)->toDateString();
                break;
            case "ano":
                $firstDate = $today->subDays(365)->toDateString();
                break;
            default:
        }

        if($request->order === "populares"){
            $query->orderBy('downloaded', 'desc');
        }else{
            if($request->order !== "todas"){
                $query->whereDate('created_at','>=',$firstDate);
            }
        }

        if(!empty($request->type)){
            $query->where('type',$request->type);
        }else{
            if($request->order !== "populares"){
                $query->orderBy('created_at', 'desc');
            }
        }

        $query->with(['category','author'=>function($query){
            $query->select('id','name');
        }]);

        $subtitles = $query->paginate(12);

        return response()->json(['success'=>$subtitles], $this->successStatus);
    }

    public function getAll(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = Subtitle::where('id','like', '%'.$request->search.'%')
                    ->orWhere('name','like', '%'.$request->search.'%')
                    ->orWhere('year','like', '%'.$request->search.'%')
                    ->orWhere('category','like', '%'.$request->search.'%')
                    ->orWhere('status','like', '%'.$request->search.'%')->with('category');
                    
        $subtitles = $query->paginate(100);

        return response()->json(['success'=>$subtitles, 'categories' => Category::all()], $this->successStatus);
    }

    public function find($id){

        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $subtitle = Subtitle::with('category')->find($id);
        $categories = Category::all();
        if($subtitle || $categories){
            return response()->json(['success'=>$subtitle, 'categories' => $categories], $this->successStatus);
        }else{
            return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);
        }
    }

    public function store(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }
        
        $validator = $this->validateSubtitle($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        $input = $request->all();
        if(empty($input['status']))
            $input['status'] = 'PENDENTE';
        
        if(empty($input['type']))
            $input['type'] = 'FILME';

        $input['author'] = $request->user()->id;
        $subtitle = Subtitle::create($input); 

        if($subtitle){
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    public function update(Request $request) 
    {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateSubtitle($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $subtitle = Subtitle::find($request->id);

        if($subtitle){
            $input = $request->all();
            if(empty($input['type']))
                $input['type'] = 'FILME';

            if($subtitle->status !== $input['status'])
                $input['created_at'] = Carbon::now();

            $subtitle->update($input);

            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }

        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    public function destroy($id) {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $subtitle = Subtitle::find($id);

        if($subtitle->delete()){
            return response()->json(['success'=>['Cadastro excluido com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }
    
    public function pendingSubtitles(Request $request) {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = Subtitle::where('status','=', "PENDENTE")
        ->Where('name','like', '%'.$request->search.'%');
        $subtitles = $query->paginate(100);

        return response()->json(['success'=>$subtitles], $this->successStatus);

    }

    private function validateSubtitle($request){
        return Validator::make($request->all(), [ 
            'name' => 'required|string', 
            'year' => 'required|integer', 
            'url' => 'required|string',
            'type' => 'nullable|string',
            'episode' => 'nullable|string',
            'image' => 'nullable', 
            'status' => [
                'nullable',
                Rule::in(['APROVADA', 'PENDENTE']),
            ], 
            'author' => 'nullable', 
            'category' => 'required'
        ]);
    }

}
