<?php
namespace App\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Utils {
    public static function update_image($model ,Request $request, $folderName, $imageFile = '') {
        $imageUri = '';
    
        if($request->hasFile('image') || !empty($imageFile)) {
            $image   = !empty($imageFile)? $imageFile : $request->file('image');
            $filename = $model->id . '.' . $image->getClientOriginalExtension();
            $imageUri = 'img/'.$folderName.'/';
            Storage::putFileAs($imageUri, $image, $filename);
            $imageUri = $imageUri . $filename;
            $model->update(['image'=> $imageUri]);
            $model->touch();
        }
        return $imageUri;
    }
}

