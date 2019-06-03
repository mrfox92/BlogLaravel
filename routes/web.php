<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


//  RUTA TESTING ORM
//  Route::get('/test-orm', 'PruebasController@testOrm');

//  RUTAS DEL API


    /*
        *   GET:    Conseguir datos o recursos
        *   POST:   Guardar datos o recursos o hacer lógica
        *   PUT:    Actualizar recursos o datos
        *   DELETE: Eliminar datos o recursos
    */

    //  Rutas de prueba
    /* Route::get('/entrada/pruebas', 'PostController@pruebas');
    Route::get('/categoria/pruebas', 'CategoryController@pruebas');
    Route::get('/usuario/pruebas', 'UserController@pruebas'); */

    //  Rutas del controlador de usuarios
    //  Route::post('/api/register', 'UserController@register');
    //  Route::post('/api/login', 'UserController@login');
