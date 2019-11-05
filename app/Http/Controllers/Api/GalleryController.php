<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Utils\Utils;
use App\Gallery;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function getAll(Request $request){
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $images = Gallery::where('name','like', '%'.$request->search.'%')
                            ->orWhere('tags','like', '%'.$request->search.'%')
                            ->paginate(100);

        return response()->json(['success'=> $images], $this->successStatus);
    }

    public function find($id){

        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $gallery = Gallery::find($id);

        if($gallery){
            return response()->json(['success'=>$gallery], $this->successStatus);
        }else{
            return response()->json(['error'=>['Imagem não encontrada']], $this->errorStatus);
        }
    }

    public function store(Request $request){
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $validator = $this->validateImage($request);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }

        $gallery = Gallery::create($request->except('image'));
        if($gallery){
            Utils::update_image($gallery, $request, 'subs');
            return response()->json(['success'=> ['Imagem cadastrada com sucesso!']], $this->successStatus);
        }
        return response()->json(['error'=> ['Erro inesperado, não foi possível salvar a imagem!']], $this->errorStatus);
    }

    public function update(Request $request) {
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        if(!$request->hasFile('image')){
            $request->merge(['image' => null]);
        }
        
        $validator = $this->validateImage($request, false);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->errorStatus);            
        }
        
        $gallery = Gallery::find($request->id);

        if($gallery){
            if($request->hasFile('image')){
                Storage::delete("/public/".$gallery->image);
                Utils::update_image($gallery, $request, 'subs');
            }
            $gallery->update($request->except('image'));
            return response()->json(['success'=>['Cadastro atualizado com sucesso']], $this->successStatus);
        }
        return response()->json(['error'=>['Imagem não encontrada']], $this->errorStatus);   
    }

    public function destroy($id) {
        if(!(
            Gate::allows('isAdmin') 
            || Gate::allows('isModerador')
        )){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $gallery = Gallery::find($id);
        
        if($gallery){
            $path = "/public/".$gallery->image;
            if(Storage::delete($path) || !Storage::exists($path)){
                $gallery->delete();
                return response()->json(['success'=>['Imagem excluída com sucesso']], $this->successStatus);
            }
            return response()->json(['error'=>['Não foi possível deletar a imagem tente novamente!']], $this->errorStatus);  
        }
        return response()->json(['error'=>['Imagem não encontrada']], $this->errorStatus);   
    }

    public function validateImage($request, $required = true){
        return Validator::make($request->all(), [ 
            'name' => 'required|max:191',
            'tags' => 'nullable|max:191',
            'image' => ($required?'required':'nullable').'|image|mimes:jpeg,png,jpg|max:1000|dimensions:max_width=650,max_height=700'
        ],[
            'image.required' => 'Imagem é obrigatória.',
            'image' => 'Imagem inválida.',
            'mimes' => 'Formatos de imagem aceitos: jpeg,png,jpg.',
            'dimensions' => 'As dimensões da imagem não devem exceder 650x700.'
        ]);
    }

}
