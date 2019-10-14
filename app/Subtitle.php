<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    protected $fillable = [
        'name', 'year', 'url', 'image', 'status', 'author', 'downloaded', 'category'
    ];

    public function category(){
        return $this->belongsTo('App\Category', 'category', 'id');
    }

    public function author(){
        return $this->belongsTo('App\User', 'author', 'id');
    }

}
