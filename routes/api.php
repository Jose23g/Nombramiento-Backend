<?php

use App\Http\Controllers\ArchivosController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\CantonController;
use App\Http\Controllers\CargaController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DeclaracionJuradaController;
use App\Http\Controllers\DetalleSolicitudController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\DocenciaController;
use App\Http\Controllers\FechaController;
use App\Http\Controllers\HorariosGrupoController;
use App\Http\Controllers\HorariosTrabajoController;
use App\Http\Controllers\PlanEstudiosController;
use App\Http\Controllers\ProfesorContoller;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\PSeisController;
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

// Rutas de autenticación
Route::prefix('auth')->controller(UsuarioController::class)->group(function () {
    Route::post('registrar', 'register');
    Route::post('login', 'login');
    Route::get('refresh', 'renueveElToken');
});

// Rutas relacionadas con la dirección
Route::group(['prefix' => 'direccion'], function () {
    Route::get('provincia', [ProvinciaController::class, 'obtenga']);
    Route::get('canton', [CantonController::class, 'obtenga']);
    Route::get('distrito', [DistritoController::class, 'obtenga']);
});

//Todas las rutas protegidas
Route::post('prueba', [PSeisController::class, 'Obtener_datos_solicitud']);
Route::get('listadoDeBancos', [BancoController::class, 'obtengaLaLista']);
Route::get('obtengaElBanco', [BancoController::class, 'obtengaPorId']);
Route::get('listadoDeFechas', [FechaController::class, 'obtengaLaListaDeFechas']);
Route::post('editarsolicitud', [CoordinadorController::class, 'Editar_solicitud_curso']);

Route::middleware('auth:api')->group(function () {

    //Rutas relacionadas a la gestion del usuario
    Route::controller(UsuarioController::class)->prefix('usuario')->group(function () {
        Route::get('perfil', 'obtenerPerfil');
        Route::post('editar', 'editarUsuario');
        Route::get('validar', 'validarToken');
    });

    //Rutas relacionada a archivos
    Route::controller(ArchivosController::class)->group(function () {
        Route::get('obtengaElArchivo', 'obtenga');
        Route::post('guardeElDocumento', 'guarde');
        Route::delete('elimineElDocumento', 'elimine');
    });

    //Rutas relacionadas a las carreras
    Route::controller(CarreraController::class)->group(function () {
        Route::get('listadoDeProfesores', 'obtengaLaListaDeProfesoresPorCarrera');
        Route::get('listadoDePlanEstudios', 'obtengaLaListaDePlanEstudiosPorCarrera');
    });

    Route::get('listadoDeCursos', [PlanEstudiosController::class, 'obtengaLaListaDeCursosPorPlanEstudio']);
    Route::get('listadoDeCargas', [CargaController::class, 'obtengaLaListaDeCargas']);
    Route::get('vigenciap6', [DocenciaController::class, 'obtener_TNombramiento_vigenciaP6']);

    Route::controller(PSeisController::class)->group(function () {
        Route::get('listarp6usuario', 'listarP6_Usuario');
        Route::post('verp6', 'Obtener_datos_P6_id');
        Route::post('crearp6', 'crearP6');
    });
    Route::controller(TrabajoController::class)->group(function () {
        Route::get('listadotrabajos', 'obtengaElListadoPorPersona');
        Route::post('editartrabajo', 'editarTrabajo');
    });
    Route::controller(ArchivosController::class)->group(function () {
        Route::get('obtengaElArchivo', 'obtenga');
        Route::post('guardeElArchivo', 'guarde');
        Route::delete('elimineElArchivo', 'elimine');
    });
    Route::controller(CarreraController::class)->group(function () {
        Route::get('listadoDeProfesores', 'obtengaLaListaDeProfesoresPorCarrera');
        Route::get('listadoDePlanEstudios', 'obtengaLaListaDePlanEstudiosPorCarrera');
    });
    Route::controller(UsuarioController::class)->group(function () {
        Route::get('coordinadorActual', 'obtengaElCoordinadorActual');
        Route::get('profesorActual', 'obtengaElProfesorActual');
        Route::get('miscarreras', 'misCarreras');
        Route::get('revoqueLosTokens', 'revoqueLosTokens');
    });
    Route::controller(DeclaracionJuradaController::class)->group(function () {
        Route::post('agregueLaDeclaracion', 'agregue');
        Route::get('muestreLaDeclaracion', 'obtengaLaUltimaDeclaracion');
    });

    //Rutas accesibles solamente para los roles de Docencia
    Route::middleware('scope:Docencia')->group(function () {
    
        //Rutas relacionadas a la gestion de Docencia
        Route::controller(DocenciaController::class)->group(function () {
            Route::post('solicitudfecha', 'Ver_Solicitud_curso_fecha');
            Route::post('/establecer-plazo', 'fechaRecepcion');
            Route::post('/comprobar', 'comprobarFechaRecepcion');
            Route::get('verpendientes', 'Listar_todas_solicitudes');
            Route::get('fechas', 'Listar_fechas_solicitudes');
            Route::post('cambiar-estado', 'cambiarEstadoSolicitud');
            Route::get('ultimafecha', 'Obtener_ultima_fecha');
            Route::post('crear-vigencia-p6', 'establecer_TNombramiento_vigenciaP6');
            Route::get('vigencia-p6', 'obtener_TNombramiento_vigenciaP6');

        });


        Route::controller(UsuarioController::class)->group(function () {
            Route::get('lista-de-usuarios', 'listar_todos_los_usuarios');
        });

    });

    //Rutas rutas para los roles de profesores
    Route::middleware('scope:Profesor')->group(function () {


        Route::controller(TrabajoController::class)->group(function () {
            Route::get('listadoDeTrabajosExternos', 'obtengaElListadoDeTrabajosExternos');
            Route::get('listadoDeTrabajosInternos', 'obtengaElListadoDeTrabajosInternos');
            Route::post('trabajo', 'agregue');
            Route::post('agregueUnTrabajoInterno', 'agregueUnTrabajoInterno');
            Route::post('modifiqueUnTrabajoInterno', 'modifiqueUnTrabajoInterno');
            Route::delete('elimineUnTrabajoInterno', 'elimineUnTrabajoInterno');
            Route::get('listartrabajoshorario', 'obtengaElListadoPorPersona');
            Route::post('editartrabajo', 'modifique');
            Route::post('eliminartrabajo', 'elimine');
            Route::post('buscartrabajo', 'obtengaPorId');
        });


        Route::controller(HorariosTrabajoController::class)->group(function () {
            Route::get('listadoHorarioTrabajos', 'obtengaLaLista');
            Route::post('agregueElHorarioDelTrabajo', 'agregue');
            Route::delete('elimineElHorarioDeTrabajo', 'elimineElHorario');
        });

        Route::controller(DeclaracionJuradaController::class)->group(function () {
            Route::post('agregueLaDeclaracion', 'agregue');
            Route::get('muestreLaDeclaracion', 'obtengaLaUltimaDeclaracion');
        });

        Route::get('p6', [ProfesorContoller::class, 'previsualizarP6']);
    });

    //Rutas para los roles de Coordinador
    Route::middleware('scope:Coordinador')->group(function () {


        Route::controller(SolicitudCursoController::class)->group(function () {
            Route::get('listadoSolicitudCursos', 'obtengaLaLista');
            Route::get('cursoscarrera', [CursoController::class, 'cursosCarrera']);
            Route::get('marqueComoPendienteLaSolicitudDeCursos', 'marqueComoPendiente');
            Route::post('agregueLaSolicitudDeCursos', 'agregue');
            Route::delete('elimineLaSolicitudDeCursos', 'elimineLaSolicitud');
            Route::post('addtrabajo', [TrabajoController::class, 'agregue']);
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
            Route::get('listaProfesores', 'listaProf');
            Route::get('ver-p6', 'previsualizarP6');
            Route::post('asignar-carrera', 'incorporar_a_carrera');
        });


        Route::controller(CursoController::class)->group(function () {
            Route::post('addcurse', 'agregueUnCurso');
            Route::post('editarcurso', 'editarCurso');
        });

        Route::post('addplan', [PlanEstudiosController::class, 'agregue']);
        Route::get('grados', [PlanEstudiosController::class, 'listargrados_plan']);
        Route::post('crearp6', [PSeisController::class, 'crearP6']);

    });

});
