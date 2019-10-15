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
}
