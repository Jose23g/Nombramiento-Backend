<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CursoController extends Controller
{
    public function agregueUnCurso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sigla' => 'required|unique:cursos,sigla',
            'nombre' => 'required',
            'creditos' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
            'unique' => 'Ya existe un valor en la columna :attribute similar al ingresado.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        Curso::create(['sigla' => $validatedData['sigla'], 'nombre' => $validatedData['nombre'], 'creditos' => $validatedData['creditos']]);

        return response()->json(['message' => 'Se ha registrado el curso con exito'], 200);
    }
}
