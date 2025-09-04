<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//ruta libres auth
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

//ruta protegida auth
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/current-user', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});



// // Rutas de Logueo y Registro
// Route::post('/register', [AuthController::class, 'register'])->middleware('auth:api');
// Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:api')->get('/current-user', [AuthController::class, 'getCurrentUser']);