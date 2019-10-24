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
        $all = User::withCount('subtitles')->orderBy('subtitles_count','desc')->get();
        $query = User::withCount('subtitles')->where('name','like', '%'.$request->search.'%')->orderBy('subtitles_count','desc');
        $user = $query->paginate(100);
        $allArray = $all->toArray();
        $position = 1;
        $count = $allArray[0]['subtitles_count'];
        foreach($allArray as &$value){
            if($value['subtitles_count'] < $count){
                $count = $value['subtitles_count'];
                $position++;
            }
            $value = array_merge($value,['position' => $position]);
        }
        unset($value);

        $user->data = $user->each(function ($item) use ($allArray) {
            foreach($allArray as $valueItem){
                if($item->id === $valueItem['id']){
                    $item->id = $valueItem['position'];
                    break;
                }
            }
        });
        return response()->json(['success'=>$user], $this->successStatus);
    }

}
