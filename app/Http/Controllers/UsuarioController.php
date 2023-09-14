<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function login(Request $request){

        return response()->json("Implementar metodo de login");
    }

    public function register (Request $request){
        return response()->json("Implementar metodo de registrar");
    }
}
