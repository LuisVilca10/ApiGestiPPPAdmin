<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Modules\ModuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//ruta libres auth
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

//ruta protegidas auth
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/current-user', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});


//ruta protegida parent-module y module
Route::middleware(['auth:api',])->group(function () {
    // Rutas ParentModuleController
    // Route::prefix('parent-module')->group(function () {
    //     Route::get('/', [ParentModuleController::class, 'listPaginate']);  // Listar con paginación
    //     Route::get('/list', [ParentModuleController::class, 'list']);  // Listar sin paginación
    //     Route::get('/listar', [ParentModuleController::class, 'listar']);  // Otra lista
    //     Route::get('/list-detail-module-list', [ParentModuleController::class, 'listDetailModuleList']);  // Detalles de módulos
    //     Route::post('/', [ParentModuleController::class, 'store']);  // Crear nuevo módulo padre
    //     Route::get('/{id}', [ParentModuleController::class, 'show']);  // Mostrar módulo padre específico
    //     Route::put('/{id}', [ParentModuleController::class, 'update']);  // Actualizar módulo padre
    //     Route::delete('/{id}', [ParentModuleController::class, 'destroy']);  // Eliminar módulo padre
    // });

    // Rutas ModuleController
    Route::prefix('module')->group(function () {
        Route::get('/', [ModuleController::class, 'index']); // Ruta para paginación
        Route::get('/menu', [ModuleController::class, 'menu']);  // Obtener menú
        Route::post('/', [ModuleController::class, 'store']);  // Crear nuevo módulo
        Route::get('/{id}', [ModuleController::class, 'show']);  // Ver módulo específico
        Route::put('/{id}', [ModuleController::class, 'update']);  // Actualizar módulo
        Route::delete('/{id}', [ModuleController::class, 'destroy']);  // Eliminar módulo
    });
});


// // Rutas de Logueo y Registro
// Route::post('/register', [AuthController::class, 'register'])->middleware('auth:api');
// Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:api')->get('/current-user', [AuthController::class, 'getCurrentUser']);