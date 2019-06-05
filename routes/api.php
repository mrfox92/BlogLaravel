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

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */


//  =====================
//      RUTAS DEL API
//  =====================

    //  Rutas del controlador de usuarios
    Route::post('/register', 'UserController@register');
    Route::post('/login', 'UserController@login');
    Route::put('/user/update', 'UserController@update');
    Route::post('/user/upload', 'UserController@upload')->middleware(['api.auth']);
    Route::get('/user/image/{filename}', 'UserController@getImage');
    Route::get('/user/profile/{id}', 'UserController@profile');

    //  Rutas del controlador de categorias (Rutas automaticas resource)
    Route::resource('/category', 'CategoryController');
    //  Rutas del controlador de entradas
    Route::resource('/post', 'PostController');
    Route::post('/post/upload', 'PostController@upload');
    Route::get('/post/image/{filename}', 'PostController@getImage');
    Route::get('/post/category/{category}', 'PostController@getPostsByCategory');
    Route::get('/post/user/{user}', 'PostController@getPostsByUser');
