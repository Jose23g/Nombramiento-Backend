<?php

use App\Http\Controllers\BancoController;
use App\Http\Controllers\CantonController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DetalleSolicitudController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\DocenciaController;
use App\Http\Controllers\HorariosGrupoController;
use App\Http\Controllers\PlanEstudiosController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\SolicitudCursoController;
use App\Http\Controllers\SolicitudGrupoController;
use App\Http\Controllers\TrabajoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Ruta de prueba protegida por autenticación Passport

Route::prefix('auth')->controller(UsuarioController::class)->group(function () {
    Route::post('registrar', 'register');
    Route::post('login', 'login');
});

Route::group(['prefix' => 'direccion'], function () {
    Route::get('provincia', [ProvinciaController::class, 'obtenga']);
    Route::get('canton', [CantonController::class, 'obtenga']);
    Route::get('distrito', [DistritoController::class, 'obtenga']);
});

Route::middleware('auth:api')->prefix('usuario')->controller(UsuarioController::class)->group(function () {
    Route::get('perfil', 'obtenerUsuario');
    Route::post('editar', 'editeUsuario');
    Route::get('validar', 'validartoken');
});

Route::middleware('auth:api')->group(function () {
    Route::middleware('scope:Docencia')->controller(DocenciaController::class)->group(function () {
        Route::post('solicitudfecha', 'Ver_Solicitud_curso_fecha');
        Route::post('/establecer-plazo', 'fechaRecepcion');
        Route::post('/comprobar', 'comprobarFechaRecepcion');
        Route::get('vertodas', 'Listar_todas_solicitudes');
        Route::get('fechas', 'Listar_fechas_solicitudes');
        Route::post('cambiar-estado', 'cambiarEstadoSolicitud');
    });

    Route::middleware('scope:Profesor')->group(function () {
        Route::post('trabajo', [TrabajoController::class, 'Agregar_trabajo']);
    });

    Route::middleware('scope:Coordinador')->group(function () {
        Route::get('listadoSolicitudCursos', [SolicitudCursoController::class, 'obtengaLaLista']);
        Route::get('listadoDetalleSolicitud', [DetalleSolicitudController::class, 'obtengaLaLista']);
        Route::get('listadoSolicitudGrupos', [SolicitudGrupoController::class, 'obtengaLaLista']);
        Route::get('listadoHorarioGrupos', [HorariosGrupoController::class, 'obtengaLaLista']);
        Route::controller(CoordinadorController::class)->group(function () {
            Route::post('solicitud', 'Solicitud_de_curso');
            Route::get('ultimasolicitud', 'ultimaSolicitud');
        });
        Route::post('addplan', [PlanEstudiosController::class, 'agregue']);
        Route::controller(CursoController::class)->group(function () {
            Route::post('addcurse', 'agregueUnCurso');
            Route::get('getcurse', 'obtengaPorPlanDeEstudio');
        });
    });
});

Route::get('bancos', [BancoController::class, 'obtengaLaLista']);
Route::get('getprof', [CarreraController::class, 'muestreLosProfesores']);

Route::post('editarsolicitud', [CoordinadorController::class, 'Editar_solicitud_curso']);

