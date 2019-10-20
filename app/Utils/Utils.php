<?php
namespace App\Utils;
use Illuminate\Http\Request;

class Utils {
    public static function update_image($model ,Request $request, $folderName) {
        $imageUri = '';
    
        if($request->hasFile('image')) {
            $image   = $request->file('image');
            $filename = $model->id . '.' . $image->getClientOriginalExtension();
            $imageUri = 'img/'.$folderName.'/';
            $request->image->move($imageUri, $filename);
            $model->image = $imageUri . $filename;
            $model->save();
        }
        return $imageUri;
    }
}

