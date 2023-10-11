<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PlanEstudios;

class PlanEstudiosController extends Controller
{
    public function agregue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_carrera' => 'required',
            'id_grado' => 'required',
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
}