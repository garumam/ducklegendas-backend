<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Subtitle;
use App\Category;

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

//ROTA DE TESTE DE RELACIONAMENTOS ENTRE TABELAS
Route::get('/relacionamento', function (Request $request) {

    $categories = Category::with('subtitles')->get();
    $subtitles = Subtitle::with('category', 'author')->get();
    $users = User::with('subtitles.category', 'subtitles.author')
        ->has('subtitles')->get();

    return response()->json([
        'categories' =>$categories,
        'subtitles' =>$subtitles,
        'users' =>$users
    ],200); 
});

// GRUPO DE ROTAS PARA CONTROLADORES DENTRO DA PASTA Api/
Route::group(['namespace' => 'Api'],function () {

    Route::get('subtitles/list', 'SubtitleController@list');
    Route::get('subtitles/downloaded', 'SubtitleController@downloaded');
    Route::get('subtitles/andamento/list', 'SubtitleProgressController@list'); 
    Route::get('rankings/list', 'RankingController@list');
    Route::get('categories/list', 'CategoryController@list');
    Route::post('contact', 'ContactController@send');

    Route::middleware('auth:api')->group(function () {
        Route::group(['prefix' => 'categories'],function () {
            Route::get('/{id}', 'CategoryController@find');
            Route::get('/', 'CategoryController@getAll');
            Route::post('/store', 'CategoryController@store');
            Route::patch('/{id}', 'CategoryController@update');
            Route::delete('/{id}', 'CategoryController@destroy');
        });
        Route::group(['prefix' => 'subtitles'],function () {
            Route::get('/{id}', 'SubtitleController@find');
            Route::get('/', 'SubtitleController@getAll');
            Route::post('/store', 'SubtitleController@store');
            Route::patch('/{id}', 'SubtitleController@update');
            Route::delete('/{id}', 'SubtitleController@destroy');
        });
        Route::group(['prefix' => 'progress'],function () {
            Route::get('/{id}', 'SubtitleProgressController@find');
            Route::get('/', 'SubtitleProgressController@getAll');
            Route::post('/store', 'SubtitleProgressController@store');
            Route::patch('/{id}', 'SubtitleProgressController@update');
            Route::delete('/{id}', 'SubtitleProgressController@destroy');
        });

        Route::group(['prefix' => 'rankings'],function () {
            Route::get('/', 'RankingController@getAll');
        });
        Route::group(['prefix' => 'pending'],function () {
            Route::get('/', 'SubtitleController@pendingSubtitles');
        });
        Route::group(['prefix' => 'gallery'],function () {
            Route::get('/{id}', 'GalleryController@find');
            Route::get('/', 'GalleryController@getAll');
            Route::post('/store', 'GalleryController@store');
            Route::patch('/{id}', 'GalleryController@update');
            Route::delete('/{id}', 'GalleryController@destroy');
        });
    });
});

// GRUPO DE ROTAS PARA CONTROLADORES DENTRO DA PASTA Api/Auth/
Route::group(['namespace' => 'Api\Auth'],function () {
    
    //ROTAS DE AUTENTICAÇÃO
    Route::post('login', 'UserController@login');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'UserController@logout');
        Route::group(['prefix' => 'users'],function () {
            Route::get('/{id}', 'UserController@find');
            Route::get('/', 'UserController@getAll');
            Route::post('/store', 'UserController@store');
            Route::patch('/{id}', 'UserController@update');
            Route::delete('/{id}', 'UserController@destroy');
        });
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

// ROTA PARA DELETAR TOKENS DE AUTENTICAÇÃO
Route::get('error', function (){
    DB::table('oauth_access_tokens')->whereDate('expires_at','<',Carbon::now()->toDateTimeString())->delete();
    return response()->json(['error' => ['Não autenticado!']], 401);
})->name('error');
