<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PruebasController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\ApiAuthMiddleware;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/animales',[PruebasController::class, 'index']);

Route::get('/test-orm',[PruebasController::class, 'testORM']);


/*METODOS DEL HTTP
    GET: CONSEGUIR DATOS O RECURSOS
    POST: GUARDAR DATOS O RECURSOS O HACER LOGICA
    PUT: ACTUALIZAR DATOS O RECURSOS
    DELETE: BORRAR DATOS O RECURSOS
*/

//RUTAS DE PRUEBA
// Route::get('/postPrueba', [PostController::class, 'pruebas']);
// Route::get('/categoryPrueba', [CategoryController::class, 'pruebas']);
// Route::get('/userPrueba', [UserController::class, 'pruebas']);


//RUTAS DE API

/**
 * RUTAS DE USER
 */
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);
Route::put('/api/user/update', [UserController::class, 'update']);
Route::post('/api/user/upload', [UserController::class, 'upload'])->middleware('api.auth');
Route::get('/api/user/avatar/{filename}', [UserController::class, 'getImage']);
Route::get('/api/user/detail/{id}', [UserController::class, 'detail']);


/**
 * RUTAS DE CATEGORY
 */

Route::resource('/api/category',CategoryController::class);

/**
 * RTAS DEL POST(PUBLICACIONES)
 */

Route::resource('/api/post',PostController::class);
Route::post('/api/post/upload', [PostController::class, 'upload']);
Route::get('/api/post/image/{filename}', [PostController::class, 'getImage']);
Route::get('/api/post/category/{id}', [PostController::class, 'getPostByCategory']);
Route::get('/api/post/user/{id}', [PostController::class, 'getPostByUser']);