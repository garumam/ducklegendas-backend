<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// GRUPO DE ROTAS PARA CONTROLADORES DENTRO DA PASTA Api/Auth/
Route::group(['namespace' => 'Api\Auth'],function () {
    
    //ROTAS DE AUTENTICAÇÃO
    Route::post('login', 'UserController@login');
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', 'UserController@logout');
        Route::post('register', 'UserController@register');
    });

    //ROTAS DE VERIFICAÇÃO DE E-MAIL
    Route::group(['prefix' => 'email'], function () {  
        Route::get('verify/{id}', 'VerificationController@verify')->name('verification.verify');
        Route::get('resend', 'VerificationController@resend')->name('verification.resend');
        Route::get('notice', 'VerificationController@notice')->name('verification.notice');
    });

    //ROTAS DE TROCA DE SENHA
    Route::group(['prefix' => 'password'], function () {    
        Route::post('create', 'PasswordResetController@create');
        Route::post('reset', 'PasswordResetController@reset');
    });

});

// // GRUPO DE ROTAS PARA CONTROLADORES DENTRO DA PASTA Api/
// Route::middleware('auth:api')->group(['namespace' => 'Api'], function () {
    
// });

// ROTA PARA DELETAR TOKENS DE AUTENTICAÇÃO
Route::get('error', function (){
    DB::table('oauth_access_tokens')->whereDate('expires_at','<',Carbon::now()->toDateTimeString())->delete();
    return response()->json(['error' => 'unauthenticated'], 401);
})->name('error');
