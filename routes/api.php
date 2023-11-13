<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\CantonController;
use App\Http\Controllers\CargaController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DetalleSolicitudController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\DocenciaController;
use App\Http\Controllers\FechaController;
use App\Http\Controllers\HorariosGrupoController;
use App\Http\Controllers\PlanEstudiosController;
use App\Http\Controllers\ProfesorContoller;
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
    Route::get('listadoDeProfesores', [CarreraController::class, 'obtengaLaListaDeProfesoresPorCarrera']);
    Route::get('listadoDePlanEstudios', [CarreraController::class, 'obtengaLaListaDePlanEstudiosPorCarrera']);
    Route::get('listadoDeCursos', [PlanEstudiosController::class, 'obtengaLaListaDeCursosPorPlanEstudio']);
    Route::get('listadoDeCargas', [CargaController::class, 'obtengaLaListaDeCargas']);
    Route::get('coordinadorActual', [UsuarioController::class, 'obtengaElCoordinadorActual']);

    Route::middleware('scope:Docencia')->controller(DocenciaController::class)->group(function () {
        Route::post('solicitudfecha', 'Ver_Solicitud_curso_fecha');
        Route::post('/establecer-plazo', 'fechaRecepcion');
        Route::post('/comprobar', 'comprobarFechaRecepcion');
        Route::get('verpendientes', 'Listar_todas_solicitudes');
        Route::get('fechas', 'Listar_fechas_solicitudes');
        Route::post('cambiar-estado', 'cambiarEstadoSolicitud');
        Route::get('ultimafecha', 'Obtener_ultima_fecha');
    });

    Route::middleware('scope:Profesor')->group(function () {
        Route::controller(TrabajoController::class)->group(function () {
            Route::post('trabajo', 'agregue');
            Route::get('listartrabajoshorario', 'obtengaElListadoPorPersona');
            Route::post('editartrabajo', 'modifique');
            Route::post('eliminartrabajo', 'elimine');
            Route::post('buscartrabajo', 'obtengaPorId');
        });
        Route::controller(ActividadController::class)->group(function () {

            Route::post('agregartrabajofinal', 'Agregar_trabajofinal_graduacion');
            Route::get('listartrabajofinal', 'Listar_trabajosfinal_graduacion');
            Route::post('buscartrabajofinal', 'Buscar_trabajofinal_graduacion');
            Route::post('agregarproyectoaccion', 'Agregar_proyecto_accion');
            Route::get('listarproyectoaccion', 'Listar_proyectos_accion');
            Route::post('buscarproyectoaccion', 'Buscar_proyectos_accion');
            Route::post('agregarcargoDAC', 'Agregar_cargo_DAC');
            Route::get('listarcargoDAC', 'Listar_cargos_DAC');
            Route::get('buscarcargoDAC', 'Buscar_cargo_DAC');
            Route::post('agregarotralabor', 'Agregar_otra_labor');
            Route::get('listarotraslabores', 'Listar_otras_labores');
            Route::get('buscarotralabor', 'Buscar_otra_labor');
        });

        Route::get('p6', [ProfesorContoller::class, 'previsualizarP6']);
    });

    Route::middleware('scope:Coordinador')->group(function () {
        Route::controller(SolicitudCursoController::class)->group(function () {
            Route::get('listadoSolicitudCursos', 'obtengaLaLista');
            Route::get('marqueComoPendienteLaSolicitudDeCursos', 'marqueComoPendiente');
            Route::post('agregueLaSolicitudDeCursos', 'agregue');
            Route::delete('elimineLaSolicitudDeCursos', 'elimineLaSolicitud');
        });
        Route::controller(DetalleSolicitudController::class)->group(function () {
            Route::get('listadoDetalleSolicitud', 'obtengaLaLista');
            Route::post('agregueElDetalleDeSolicitud', 'agregue');
            Route::delete('elimineElDetalleDeSolicitud', 'elimineElDetalle');
        });
        Route::controller(SolicitudGrupoController::class)->group(function () {
            Route::get('listadoSolicitudGrupos', 'obtengaLaLista');
            Route::post('agregueLaSolicitudDeGrupos', 'agregue');
            Route::delete('elimineLaSolicitudDeGrupos', 'elimineLaSolicitud');
        });
        Route::controller(HorariosGrupoController::class)->group(function () {
            Route::get('listadoHorarioGrupos', 'obtengaLaLista');
            Route::post('agregueElHorarioDelGrupo', 'agregue');
            Route::delete('elimineElHorarioDeGrupo', 'elimineElHorario');
        });

        Route::controller(CoordinadorController::class)->group(function () {
            Route::post('solicitud', 'Solicitud_de_curso');
            Route::get('ultimasolicitud', 'ultimaSolicitud');
            Route::get('solicitud-profesores', 'obtenerProfesoresdeUltimaSolicitud');
            Route::get('ver-p6', 'previsualizarP6');
        });
        Route::post('addplan', [PlanEstudiosController::class, 'agregue']);
        Route::controller(CursoController::class)->group(function () {
            Route::post('addcurse', 'agregueUnCurso');
        });
    });
});

Route::get('bancos', [BancoController::class, 'obtengaLaLista']);
Route::get('listadoDeFechas', [FechaController::class, 'obtengaLaListaDeFechas']);

Route::post('editarsolicitud', [CoordinadorController::class, 'Editar_solicitud_curso']);
