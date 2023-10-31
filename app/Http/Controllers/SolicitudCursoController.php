<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudCursoController extends Controller
{
    public function agregue(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'coordinador_id' => 'required',
                'carrera_id' => 'required',
                'fecha_solicitud_id' => 'required',
            ], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $solicitudCurso = SolicitudCurso::create([
            'coordinador_id' => $request->coordinador_id,
            'carrera_id' => $request->carrera_id,
            'estado_id' => 2,
            'fecha_solicitud_id' => $request->fecha_solicitud_id,
            'observacion' => $request->observacion,
            'carga_total' => 0,
        ]);

        return response()->json(['Message' => 'Se ha registrado con éxito', 'solicitud_curso_id' => $solicitudCurso->id], 200);
    }

    public function obtengaLaLista(Request $request)
    {
        $usuario = $request->user();
        $solicitudesCurso = $usuario->solicitudCursos()->with(['coordinador.persona', 'carrera', 'estado', 'fechaSolicitud'])->get();

        return response()->json($solicitudesCurso, 200);
    }
}
