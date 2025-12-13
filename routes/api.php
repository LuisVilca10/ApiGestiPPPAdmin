<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\RoleController;
use App\Http\Controllers\Api\Modules\ModuleController;
use App\Http\Controllers\Api\Modules\ParentModuleController;
use App\Http\Controllers\Api\PPP\DocumentController;
use App\Http\Controllers\Api\PPP\PracticeController;
use Illuminate\Support\Facades\Route;

// **********************************************RUTAS LIBRES DE AUTH ********************************************************************
Route::post('/login', [AuthController::class, 'login']);


// //ruta protegidas auth
// Route::group([
//     'middleware' => 'auth:api',
//     'prefix' => 'auth'
// ], function ($router) {
//     Route::post('/register', [AuthController::class, 'register'])->name('register');


// });


// **********************************************RUTAS DE USUARIOS ********************************************************************

Route::middleware('auth:api')->group(function () {
    Route::get('/perfil', [AuthController::class, 'perfil']);
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']); // Esta es la ruta que falta
    Route::post('/upload-photo', [AuthController::class, 'uploadPhoto']); // Esta es la ruta que falta
});


//ruta protegida parent-module y module
Route::middleware(['auth:api', 'role:Admin|Estudiante'])->group(function () {
    // Rutas ParentModuleController
    Route::prefix('parent-module')->group(function () {
        Route::get('/', [ParentModuleController::class, 'listPaginate']);  // Listar con paginación
        Route::get('/list', [ParentModuleController::class, 'list']);  // Listar sin paginación
        Route::get('/listar', [ParentModuleController::class, 'listar']);  // Otra lista
        Route::get('/list-detail-module-list', [ParentModuleController::class, 'listDetailModuleList']);  // Detalles de módulos
        Route::post('/', [ParentModuleController::class, 'store']);  // Crear nuevo módulo padre
        Route::get('/{id}', [ParentModuleController::class, 'show']);  // Mostrar módulo padre específico
        Route::put('/{id}', [ParentModuleController::class, 'update']);  // Actualizar módulo padre
        Route::delete('/{id}', [ParentModuleController::class, 'destroy']);  // Eliminar módulo padre
    });

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

// **********************************************RUTAS DE ROLES ********************************************************************

Route::prefix('role')->middleware(['auth:api', 'role:Admin|Estudiante'])->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::middleware('permission:editar_roles')->post('/', [RoleController::class, 'store']);
    Route::middleware('permission:editar_roles')->put('/{id}', [RoleController::class, 'update']);
    Route::middleware('permission:editar_roles')->delete('/{id}', [RoleController::class, 'destroy']);
    Route::middleware('permission:editar_roles')->post('/assign-role/{userId}', [RoleController::class, 'assignRole']);
    Route::middleware('role:admin')->post('/assign-modules/{roleId}', [RoleController::class, 'assignModulesToRole']);
});

// **********************************************RUTAS DE TRAMITES ********************************************************************

Route::prefix('practice')->group(function () {
    Route::post('/', [PracticeController::class, 'store']);
    Route::get('/', [PracticeController::class, 'index']);
    Route::get('/documents/{id}', [PracticeController::class, 'DocumentsByPractice']);
});


// // Rutas de Logueo y Registro
// Route::post('/register', [AuthController::class, 'register'])->middleware('auth:api');
// Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:api')->get('/current-user', [AuthController::class, 'getCurrentUser']);
