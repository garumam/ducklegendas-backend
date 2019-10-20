<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\User;

class RankingController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function getAll(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }
        
        $query = User::withCount('subtitles')->where('name','like', '%'.$request->search.'%')->orderBy('subtitles_count','desc');
        $user = $query->paginate(100);
        $user->data = $user->each(function ($item, $key) use ($request) {
            $page = $request->has('page')? $request->page : 1;
            $item->id = ($key + 1) + ($page - 1) * 100;
        });
        return response()->json(['success'=>$user], $this->successStatus);
    }

}
