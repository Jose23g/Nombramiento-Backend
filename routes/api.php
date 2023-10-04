<?php

use App\Http\Controllers\BancoController;
use App\Http\Controllers\DocenciaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CoordinadorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\CantonController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\BarrioController;

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


Route::group(['prefix' => 'direccion'], function () {
    Route::get('provincia', [ProvinciaController::class, 'obtenga']);
    Route::get('canton', [CantonController::class, 'obtenga']);
    Route::get('distrito', [DistritoController::class, 'obtenga']);
    Route::get('barrio', [BarrioController::class, 'obtenga']);
});
Route::get('bancos', [BancoController::class, 'obtengaLaLista']);
 

Route::middleware('auth:api')->prefix('usuario')->group(function(){
    Route::get('perfil', [UsuarioController::class, 'obtenerUsuario']);
    Route::post('editar', [UsuarioController::class, 'editeUsuario']);
    Route::get('validar', [UsuarioController::class, 'validartoken']);
    Route::post('solicitud', [CoordinadorController::class, 'Solicitud_de_curso']);
});

Route::get('fechas', [DocenciaController::class, 'Listar_fechas_solicitudes']);
Route::post('solicitudfecha', [DocenciaController::class, 'Ver_Solicitud_curso_fecha']);

Route::middleware('auth:api')->group(function(){
    Route::post('/establecer-plazo', [DocenciaController::class, 'fechaRecepcion'])/* ->middleware('scope:Docencia') */;
    Route::post('/comprobar', [DocenciaController::class, 'comprobarFechaRecepcion'])/* ->middleware('scope:Docencia') */;

});

Route::group(['prefix' => 'auth'], function (){
    Route::post('registrar', [UsuarioController::class, 'register']);
    Route::post('login', [UsuarioController::class, 'login']);
});



