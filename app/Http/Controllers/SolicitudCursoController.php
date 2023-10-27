<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SolicitudCursoController extends Controller
{
    public function obtengaLaLista(Request $request)
    {
        $usuario = $request->user();
        $solicitudesCurso = $usuario->solicitudCursos()->with(['coordinador', 'carrera', 'estado', 'fechaSolicitud'])->get();

        return response()->json($solicitudesCurso, 200);
    }
}
