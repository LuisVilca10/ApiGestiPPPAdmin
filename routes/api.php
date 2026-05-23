<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\RoleController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Modules\ModuleController;
use App\Http\Controllers\Api\Modules\ParentModuleController;
use App\Http\Controllers\Api\PPP\DocumentController;
use App\Http\Controllers\Api\PPP\EmpresaController;
use App\Http\Controllers\Api\PPP\PracticeController;
use App\Http\Controllers\Api\PPP\RucController;
use App\Http\Controllers\Api\PPP\VisitController;
use Illuminate\Support\Facades\Route;

// **********************************************RUTAS LIBRES DE AUTH ********************************************************************
Route::post('/login', [AuthController::class, 'login']);
Route::post('/email/send-code', [EmailVerificationController::class, 'sendCode']);
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Verificación de correo desde enlace — pública, protegida por firma del URL
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyFromEmail'])
    ->middleware('signed')
    ->name('verification.verify');


// //ruta protegidas auth
// Route::group([
//     'middleware' => 'auth:api',
//     'prefix' => 'auth'
// ], function ($router) {
//     Route::post('/register', [AuthController::class, 'register'])->name('register');


// });


// **********************************************RUTAS DE USUARIOS ********************************************************************

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/perfil', [AuthController::class, 'perfil']);
    Route::get('/current-user', [AuthController::class, 'getCurrentUser']);
    // Verificación de correo
    Route::get('/email/status', [EmailVerificationController::class, 'status']);
    Route::post('/email/send-verification', [EmailVerificationController::class, 'send']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/upload-photo', [AuthController::class, 'uploadPhoto']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});


//ruta protegida parent-module y module
Route::middleware(['auth:api', 'role:Admin|Estudiante'])->group(function () {
    // Rutas ParentModuleController
    Route::prefix('parent-module')->group(function () {
        Route::get('/', [ParentModuleController::class, 'listPaginate']);  // Listar con paginación
        Route::get('/list', [ParentModuleController::class, 'list']);  // Listar sin paginación
        Route::get('/list-detail-module-list', [ParentModuleController::class, 'listDetailModuleList']);  // Detalles de módulos
        Route::post('/', [ParentModuleController::class, 'store']);  // Crear nuevo módulo padre
        Route::get('/{id}', [ParentModuleController::class, 'show']);  // Mostrar módulo padre específico
        Route::put('/{id}', [ParentModuleController::class, 'update']);  // Actualizar módulo padre
        Route::delete('/{id}', [ParentModuleController::class, 'destroy']);  // Eliminar módulo padre
    });

    // Rutas ModuleController
    Route::prefix('module')->group(function () {
        Route::get('/', [ModuleController::class, 'index']);
        Route::get('/menu', [ModuleController::class, 'menu']);
        Route::get('/modules-selected/{roleId}/{parentModuleId}', [ModuleController::class, 'modulesSelected']);
        Route::post('/', [ModuleController::class, 'store']);
        Route::get('/{id}', [ModuleController::class, 'show']);
        Route::put('/{id}', [ModuleController::class, 'update']);
        Route::delete('/{id}', [ModuleController::class, 'destroy']);
    });
});

// **********************************************RUTAS DE USUARIOS ********************************************************************

Route::prefix('users')->middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

// **********************************************RUTAS DE ROLES ********************************************************************

Route::prefix('role')->middleware(['auth:api', 'role:Admin|Estudiante'])->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::middleware('permission:editar_roles')->post('/', [RoleController::class, 'store']);
    Route::middleware('permission:editar_roles')->put('/{id}', [RoleController::class, 'update']);
    Route::middleware('permission:editar_roles')->delete('/{id}', [RoleController::class, 'destroy']);
    Route::middleware('permission:editar_roles')->post('/assign-role/{userId}', [RoleController::class, 'assignRole']);
    Route::middleware('permission:editar_roles')->post('/remove-role/{userId}', [RoleController::class, 'removeRole']);
    Route::middleware('permission:editar_roles')->post('/assign-modules/{roleId}', [RoleController::class, 'assignModulesToRole']);
});

// **********************************************RUTAS DE EMPRESAS ********************************************************************

Route::prefix('empresa')->group(function () {
    // Admin + Estudiante: ver detalle y lista para selects
    Route::middleware(['auth:api', 'role:Admin|Estudiante'])->group(function () {
        Route::get('/list', [EmpresaController::class, 'list']);
        Route::get('/{id}', [EmpresaController::class, 'show']);
    });
    // Solo Admin: listado paginado + CRUD completo
    Route::middleware(['auth:api', 'role:Admin'])->group(function () {
        Route::get('/', [EmpresaController::class, 'index']);
        Route::post('/', [EmpresaController::class, 'store']);
        Route::put('/{id}', [EmpresaController::class, 'update']);
        Route::delete('/{id}', [EmpresaController::class, 'destroy']);
    });
});

// **********************************************RUTAS DE TRAMITES ********************************************************************

Route::prefix('practice')->middleware(['auth:api', 'role:Admin|Estudiante|Encargado de Practicas|Coordinador|Docente'])->group(function () {
    Route::get('/', [PracticeController::class, 'index']);
    Route::get('/periodos', [PracticeController::class, 'periodos']);
    Route::get('/tipos-documento', [PracticeController::class, 'tiposDocumento']);
    Route::get('/practicesforselect', [PracticeController::class, 'practicesforselect']);
    Route::get('/template/plan-practicas', [PracticeController::class, 'downloadPlanTemplate']);
    Route::get('/documents/{id}', [PracticeController::class, 'documentsByPractice']);
    Route::get('/{id}', [PracticeController::class, 'show']);
    // Solo Estudiante puede registrar su práctica
    Route::middleware('role:Admin|Estudiante')->post('/', [PracticeController::class, 'store']);
    // Estudiante y Docente suben documentos
    Route::middleware('role:Admin|Estudiante|Docente')->post('/upload-document', [PracticeController::class, 'storeDocumentPractice']);
    // Encargado de Practicas aprueba, rechaza y asigna docente
    Route::middleware('role:Admin|Encargado de Practicas')->group(function () {
        Route::post('/{id}/aprobar', [PracticeController::class, 'approve']);
        Route::post('/{id}/rechazar', [PracticeController::class, 'reject']);
        Route::post('/{id}/assign-docente', [PracticeController::class, 'assignDocente']);
    });
});

Route::prefix('documents')->middleware(['auth:api', 'role:Admin|Estudiante|Encargado de Practicas|Coordinador|Docente'])->group(function () {
    Route::get('/', [DocumentController::class, 'indexForStudent']);
    // Encargado de Practicas aprueba o rechaza documentos
    Route::middleware('role:Admin|Encargado de Practicas')->group(function () {
        Route::post('/{id}/aprobar', [DocumentController::class, 'aprobar']);
        Route::post('/{id}/rechazar', [DocumentController::class, 'rechazar']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
    });
});

// **********************************************RUTAS DE DASHBOARD ********************************************************************

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth:api', 'role:Admin|Estudiante|Encargado de Practicas|Coordinador|Docente']);

// **********************************************RUTA LOOKUP DE RUC ********************************************************************

Route::get('/ruc/{ruc}', [RucController::class, 'lookup'])
    ->middleware(['auth:api', 'role:Admin|Estudiante|Encargado de Practicas']);

// **********************************************RUTAS DE VISITAS (BITÁCORA) ********************************************************************

Route::prefix('visits')->middleware(['auth:api', 'role:Admin|Docente|Estudiante|Encargado de Practicas|Coordinador'])->group(function () {
    Route::get('/', [VisitController::class, 'index']);
    Route::get('/{id}', [VisitController::class, 'show']);
    // Docente registra y edita sus visitas
    Route::middleware('role:Admin|Docente')->group(function () {
        Route::post('/', [VisitController::class, 'store']);
        Route::put('/{id}', [VisitController::class, 'update']);
        Route::delete('/{id}', [VisitController::class, 'destroy']);
    });
});
// // Rutas de Logueo y Registro
// Route::post('/register', [AuthController::class, 'register'])->middleware('auth:api');
// Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:api')->get('/current-user', [AuthController::class, 'getCurrentUser']);
