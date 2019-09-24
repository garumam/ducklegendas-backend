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
Route::get('error', function (){
    
    DB::table('oauth_access_tokens')->whereDate('expires_at','<',Carbon::now()->toDateTimeString())->delete();

    return response()->json(['error' => 'unauthenticated'], 200);
})->name('error');

Route::group(['namespace' => 'Api'],function () {
    Route::post('login', 'Auth\UserController@login');
    Route::post('register', 'Auth\UserController@register');
    Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
    Route::get('email/notice', 'Auth\VerificationController@notice')->name('verification.notice');

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', 'Auth\UserController@logout');
    });
});


Route::group([    
    'namespace' => 'Api',    
    'middleware' => 'api',    
    'prefix' => 'password'
], function () {    
    Route::post('create', 'Auth\PasswordResetController@create');
    Route::post('reset', 'Auth\PasswordResetController@reset');
});

