<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CursoController extends Controller
{
    public function agregueUnCurso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_estudios_id' => 'required|exists:plan_estudios,id',
            'sigla' => 'required|unique:cursos,sigla',
            'nombre' => 'required',
            'creditos' => 'required',
            'grado_anual' => 'required',
            'ciclo' => 'required',
            'horas_teoricas' => 'required',
            'individual_colegiado' => 'required',
            'tutoria' => 'required',
            'horas_practicas' => 'required',
            'horas_laboratorio' => 'required'

        ], [
            'required' => 'El campo :attribute es requerido.',
            'unique' => 'Ya existe un valor en la columna :attribute similar al ingresado.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        
        try{
            $nuevocurso = Curso::create([
                'sigla' => $validatedData['sigla'],
                'nombre' => $validatedData['nombre'],
                'creditos' => $validatedData['creditos'],
                'grado_anual' => $validatedData['grado_anual'],
                'ciclo' => $validatedData['ciclo'],
                'horas_practicas' => $validatedData['horas_practicas'],
                'horas_laboratorio' => $validatedData['horas_laboratorio'],
                'individual_colegiado' => $validatedData['individual_colegiado'],
                'tutoria' => $validatedData['tutoria'],
                'horas_teoricas' => $validatedData['horas_teoricas']
            ]);

            return response()->json(['message' => 'Se ha registrado el curso con exito el curso' .$nuevocurso->nombre], 200);

        }catch(Exception $e){

            return response()->json(['error' => $e->getMessage()], 422);
        }
        
    }
}
