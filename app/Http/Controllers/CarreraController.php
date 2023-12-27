<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarreraController extends Controller
{
    public function obtengaLaListaDeProfesoresPorCarrera(Request $request)
    {
        $carrera = $request->user()->carreras->first();
        if ($carrera) {
            $profesores = $carrera->usuarios()->with('persona')->where('rol_id', 1)->get()->map(function ($profesor) {
                return ['id' => $profesor->id, 'nombre' => $profesor->persona->nombre];
            });

            return response()->json($profesores, 200);
        }

        return response()->json(['message' => 'No se encontrado'], 500);
    }

    public function obtengaLaListaDePlanEstudiosPorCarrera(Request $request)
    {
        $carrera = $request->user()->carreras->first();
        if ($carrera) {
            $planEstudios = $carrera->planEstudios()->with('grado')->get()->map(function ($planEstudio) {
                return ['id' => $planEstudio->id, 'nombre' => $planEstudio->anio . ' - ' . $planEstudio->grado->nombre,
                    'anio' => $planEstudio->anio, 'grado_id' => $planEstudio->grado_id];
            });

            return response()->json($planEstudios, 200);
        }

        return response()->json(['message' => 'No se encontrado'], 500);
    }
}
