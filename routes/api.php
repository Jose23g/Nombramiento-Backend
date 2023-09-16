<?php

use App\Http\Controllers\UsuarioController;
use Illuminate\Http\Request;
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

Route::group(['prefix' => 'auth'], function (){
    Route::post('registrar', [UsuarioController::class, 'register'])->name('registrar');
    Route::post('login', [UsuarioController::class, 'login'])->name('login');
});

