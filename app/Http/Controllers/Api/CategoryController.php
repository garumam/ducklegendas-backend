<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Category;
class CategoryController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function getAll(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = Category::where('id','like', '%'.$request->search.'%')
                    ->orWhere('name','like', '%'.$request->search.'%');
        $categories = $query->paginate(100);

        return response()->json(['success'=>$categories], $this->successStatus);
    }

    public function find($id){

        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $category = Category::find($id);

        if($category){
            return response()->json(['success'=>$category], $this->successStatus);
        }else{
            return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);
        }
    }

    public function store(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateCategory($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }

        $category = Category::create($request->all()); 
        
        if($category){
            return response()->json(['success'=>['Cadastro efetuado com sucesso']], $this->successStatus); 
        }
        return response()->json(['error'=> ['Ocorreu um problema inesperado por favor tente novamente!']], $this->errorStatus);
    }

    public function update(Request $request) {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateCategory($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $category = Category::find($request->id);

        if($category){
            $category->update($request->all());
            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    public function destroy($id) {
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $category = Category::find($id);

        if($category->delete()){
            return response()->json(['success'=>['Cadastro excluido com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Usuário não encontrado']], $this->errorStatus);   
    }

    private function validateCategory($request){
        return Validator::make($request->all(), [ 
            'name' => 'required|unique:categories'
        ]);
    }
}
