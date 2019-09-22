<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// SE FOR USAR VERIFICAÇÃO COLOCAR O MIDDLEWARE 'verified' NAS ROTAS QUE QUISER
Route::middleware(['auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'],function () {
    Route::post('login', 'Auth\UserController@login');
    Route::post('register', 'Auth\UserController@register');
    Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
    Route::get('email/notice', 'Auth\VerificationController@notice')->name('verification.notice');
});


Route::group([    
    'namespace' => 'Api',    
    'middleware' => 'api',    
    'prefix' => 'password'
], function () {    
    Route::post('create', 'Auth\PasswordResetController@create');
    Route::post('reset', 'Auth\PasswordResetController@reset');
});

Route::middleware(['auth:api'])->group(function () {

});