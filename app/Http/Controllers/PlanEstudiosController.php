<?php

namespace App\Http\Controllers;

use App\Models\PlanEstudios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanEstudiosController extends Controller
{
    public function agregue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_carrera' => 'required',
            'id_grado' => 'required',
            'fecha' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        PlanEstudios::create(['id_carrera' => $validatedData['id_carrera'], 'id_grado' => $validatedData['id_grado']]);

        return response()->json(['message' => 'Se ha registrado el plan de estudios con exito'], 200);
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
}
