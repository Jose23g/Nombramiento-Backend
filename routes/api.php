<?php

use App\Http\Controllers\UsuarioController;
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
Route::group(['prefix' => 'auth'], function (){
    Route::post('registrar', [UsuarioController::class, 'register'])->name('registrar');
    Route::post('login', [UsuarioController::class, 'login'])->name('login');
});

Route::group(['prefix' => 'dta'], function () {
    Route::group(['prefix' => 'provincia'], function () {
        Route::get('List', [ProvinciaController::class, 'obtengaLaLista']);
        Route::get('ById', [ProvinciaController::class, 'obtengaPorId']);
    });
    Route::group(['prefix' => 'canton'], function () {
        Route::get('ListByP', [CantonController::class, 'obtengaLaListaPorProvincia']);
        Route::get('ById', [CantonController::class, 'obtengaPorId']);
    });
    Route::group(['prefix' => 'distrito'], function () {
        Route::get('ById', [DistritoController::class, 'obtengaPorId']);
        Route::get('ListByPC', [DistritoController::class, 'obtengaLaListaPorProvinciaYCanton']);
    });
    Route::group(['prefix' => 'barrio'], function () {
        Route::get('ById', [BarrioController::class, 'obtengaPorId']);
        Route::get('ListByPCD', [BarrioController::class, 'obtengaLaListaPorProvinciaCantonYDistrito']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
