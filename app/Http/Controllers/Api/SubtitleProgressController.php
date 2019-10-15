<?php

namespace App\Http\Controllers\Api;

use App\SubtitleProgress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SubtitleProgressController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 403;

    public function getAll(Request $request){
        if(Gate::denies('isAdmin')){
            return response()->json(['error'=> ['Acesso negado para este conteúdo!']], $this->errorStatus);
        }

        $query = SubtitleProgress::where('id','like', '%'.$request->search.'%')
                    ->orWhere('name','like', '%'.$request->search.'%')
                    ->orWhere('percent','like', '%'.$request->search.'%')
                    ->orWhere('status','like', '%'.$request->search.'%');
                    
        $subtitles = $query->paginate(100);

        return response()->json(['success'=>$subtitles], $this->successStatus);
    }
}
