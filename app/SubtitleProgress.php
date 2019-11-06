<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubtitleProgress extends Model
{
    protected $fillable = ['name','percent','author'];

    public function author(){
        return $this->belongsTo('App\User', 'author', 'id');
    }
}
