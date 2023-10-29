<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarreraController extends Controller
{
    public function obtengaLaListaDeProfesoresPorCarrera(Request $request)
    {
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $carrera = Carrera::find($request->id);
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
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $carrera = Carrera::find($request->id);
        if ($carrera) {
            $planEstudios = $carrera->planEstudios()->with('grado')->get()->map(function ($planEstudio) {
                return ['id' => $planEstudio->id, 'nombre' => $planEstudio->anio.' - '.$planEstudio->grado->nombre];
            });

            return response()->json($planEstudios, 200);
        }

        return response()->json(['message' => 'No se encontrado'], 500);
    }
}
