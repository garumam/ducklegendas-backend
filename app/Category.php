<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function subtitles(){
        return $this->hasMany('App\Subtitle', 'category', 'id');
    }

}
