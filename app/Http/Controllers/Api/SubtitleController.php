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
                $firstDate = $today->subWeek()->toDateString();
                break;
            case "mes":
                $firstDate = $today->subMonth()->toDateString();
                break;
            case "semestre":
                $firstDate = $today->subMonths(6)->toDateString();
                break;
            case "ano":
                $firstDate = $today->subYear()->toDateString();
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

        if(!empty($request->category)){
            $query->where('category',$request->category);
        }

        $query->with(['category','author'=>function($query){
            $query->select('id','name');
        }]);

        $subtitles = $query->paginate(12);

        return response()->json(['success'=>$subtitles], $this->successStatus);
    }

    public function downloaded(Request $request){
        $subtitle = Subtitle::find($request->id);

        if($subtitle){
            $subtitle->update(['downloaded'=>$subtitle->downloaded+1]);
            return response()->json(['success'=>'Atualizado com sucesso!'], $this->successStatus);
        }
    }

    public function getAll(Request $request){
        if(!Gate::any(['isAdmin','isModerador','isAutor','isLegender'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }
        
        $query = Subtitle::where(function($q) use ($request) {
            $q->where('id','like', '%'.$request->search.'%')
            ->orWhere('name','like', '%'.$request->search.'%')
            ->orWhereHas('category',function($c) use ($request) {
                $c->where('name','like', '%'.$request->search.'%');
            })
            ->orWhere('status','like', '%'.$request->search.'%')
            ->orWhereHas('author',function($a) use ($request) {
                $a->where('name','like', '%'.$request->search.'%');
            });
        });
        
        if(Gate::allows('isLegender')){
            $user = $request->user();
            $query->where('author', $user->id);
        }
        $query->with('category','author');
        
        $subtitles = $query->paginate(100);

        // $subtitles = $subtitles->toArray();
        // $arrayData = $subtitles['data'];

        // $subtitles['data'] = collect($arrayData)->map(function($item) {
        //     return array_merge($item, ['author'=>$item['author']['name']]);
        // });
        
        return response()->json(['success'=>$subtitles, 'categories' => Category::all()], $this->successStatus);
    }
    public function findFront($id){
        
        $subtitle = Subtitle::where('status','APROVADA')
                        ->with(['category','author'])
                        ->find($id);

        if($subtitle){
    
            $subtitle = $subtitle->toArray();
            $subtitle['author'] = $subtitle['author']['name'];
            
            return response()->json(['success'=>$subtitle], $this->successStatus);
        }else{
            return response()->json(['error'=>['Legenda não encontrada']], $this->errorStatus);
        }
    }

    public function find(Request $request, $id){
        if(!Gate::any(['isAdmin','isModerador','isAutor','isLegender'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $subtitle = Subtitle::with(['category','author'])->find($id);

        $categories = Category::all();
        if($categories){

            if($subtitle){
                if(Gate::allows('isLegender')){
                    $user = $request->user();
                    if($subtitle->author !== $user->id){
                        return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
                    }
                }
        
                // $subtitle = $subtitle->toArray();
                // $subtitle['author'] = $subtitle['author']['name'];
            }

            return response()->json(['success'=>$subtitle, 'categories' => $categories], $this->successStatus);
        }else{
            return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);
        }
    }

    public function store(Request $request){
        if(!Gate::any(['isAdmin','isModerador','isAutor','isLegender'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }
        
        $validator = $this->validateSubtitle($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        $input = $request->all();

        if(Gate::allows('isLegender')){
            if($input['status'] !== 'PENDENTE'){
                return response()->json(['error'=> ['Você só tem permissão para criar legendas pendentes!']], $this->errorStatus);
            }
        }

        if(empty($input['status'])){
            $input['status'] = 'PENDENTE';
        }
        
        if(empty($input['type']))
            $input['type'] = 'FILME';

        $result = $this->getCustomUrl($input);

        if($result["status"] === 'error') {
            return response()->json(['error'=> [$result["message"]]], $this->errorStatus);
        } else {
            $input['url'] = $result["shortenedUrl"];
        }

        $input['author'] = $request->user()->id;
        $subtitle = Subtitle::create($input); 

        if($subtitle){
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    public function getCustomUrl($input){
        $long_url = urlencode($input['url']);
        $api_token = 'f425f338ee311727db12017f1dfd0a31346f0475';
        $nomeLegenda = str_replace(" ","-",$input['name']);
        $nomeLegenda = substr($nomeLegenda,0,16);
        $alias = preg_replace('/[^a-z0-9\-]/i', '', $nomeLegenda).'-'.Carbon::now()->format('dmyHis');
        $api_url = "http://shrinkme.io/api?api=$api_token&url=$long_url&alias=$alias";
        $result = @json_decode(file_get_contents($api_url),TRUE);
        return $result;
    }

    public function update(Request $request) 
    {
        if(!Gate::any(['isAdmin','isModerador','isAutor','isLegender'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateSubtitle($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $subtitle = Subtitle::find($request->id);

        if($subtitle){
            $input = $request->all();

            if(Gate::allows('isLegender')){
                if($subtitle->status === 'APROVADA'){
                    return response()->json(['error'=> ['Você não tem permissão de editar legendas aprovadas!']], $this->errorStatus);
                }else{
                    if($input['status'] !== 'PENDENTE'){
                        return response()->json(['error'=> ['Você só tem permissão para criar legendas pendentes!']], $this->errorStatus);
                    }
                }
            }

            if(Gate::allows('isAutor') && $subtitle->author !== $request->user()->id){
                if($subtitle->status === 'APROVADA'){
                    return response()->json(['error'=> ['Você não tem permissão de editar legendas aprovadas de outro usuário!']], $this->errorStatus);
                }
            }

            if(empty($input['type']))
                $input['type'] = 'FILME';

            if($subtitle->status !== $input['status'])
                $input['created_at'] = Carbon::now();

            $result = $this->getCustomUrl($input);

            if($result["status"] === 'error') {
                return response()->json(['error'=> [$result["message"]]], $this->errorStatus);
            } else {
                $input['url'] = $result["shortenedUrl"];
            }

            $subtitle->update($input);

            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }

        return response()->json(['error'=>['Legenda não encontrada']], $this->errorStatus);   
    }

    public function destroy(Request $request, $id) {
        if(!Gate::any(['isAdmin','isModerador','isAutor','isLegender'])){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $subtitle = Subtitle::find($id);

        if($subtitle){
            if(Gate::allows('isLegender')){
                if($subtitle->status === 'APROVADA'){
                    return response()->json(['error'=> ['Você não tem permissão de deletar legendas aprovadas!']], $this->errorStatus);
                }
            }

            if(Gate::allows('isAutor') && $subtitle->author !== $request->user()->id){
                if($subtitle->status === 'APROVADA'){
                    return response()->json(['error'=> ['Você não tem permissão de deletar legendas aprovadas de outro usuário!']], $this->errorStatus);
                }
            }

            $subtitle->delete();
            return response()->json(['success'=>['Cadastro excluido com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Legenda não encontrada']], $this->errorStatus);   
    }
    
    public function pendingSubtitles(Request $request) {
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
            || Gate::allows('isAutor')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = Subtitle::where('status','=', "PENDENTE")
        ->where(function ($q) use ($request) {
            $q->where('name','like', '%'.$request->search.'%')
            ->orWhereHas('category',function($c) use ($request) {
                $c->where('name','like', '%'.$request->search.'%');
            })
            ->orWhereHas('author',function($a) use ($request) {
                $a->where('name','like', '%'.$request->search.'%');
            });
        })
        ->with('author','category');
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
            'note' => 'nullable|string',
            'status' => [
                'nullable',
                Rule::in(['APROVADA', 'PENDENTE']),
            ],
            'category' => 'required'
        ]);
    }

}
