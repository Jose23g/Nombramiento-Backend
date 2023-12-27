<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\PlanEstudios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanEstudiosController extends Controller
{
    public function agregue(Request $request)
    {
        $carrera = $request->user()->carreras->first();
        $validator = Validator::make($request->all(), [
            'grado_id' => 'required',
            'anio' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        $nuevoplan = PlanEstudios::create(['carrera_id' => $carrera->id, 'grado_id' => $validatedData['grado_id'],
            'anio' => $validatedData['anio']]);

        return response()->json(['message' => 'Se ha ingresado el plan',
            'plan' => $nuevoplan], 200);
    }

    public function obtengaLaListaDeCursosPorPlanEstudio(Request $request)
    {
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $planEstudio = PlanEstudios::find($request->id);
        if ($planEstudio) {
            $cursos = $planEstudio->cursos;

            return response()->json($cursos, 200);
        }

        return response()->json(['message' => 'No se encontrado'], 500);
    }

    public function listargrados_plan(Request $request)
    {
        return Grado::all('id', 'nombre');
    }
}
