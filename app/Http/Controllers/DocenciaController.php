<?php

namespace App\Http\Controllers;

use App\Models\FechaSolicitud;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DocenciaController extends Controller
{
    public function fechaRecepcion(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'anio' => 'required',
                    'semestre' => 'required',
                    'fecha_inicio' => 'required',
                    'fecha_fin' => 'required',
                    'nombre' => 'required',
                ],
                [
                    'anio.required' => 'Es anio no puede estar vacío',
                    'semestre.required' => 'El semestre no puede estar vacío',
                    'fecha_inicio.required' => 'Es necesario establecer una fecha de inicio',
                    'fecha_fin.required' => 'Es necesario establecer una fecha de final',
                    'nombre.required' => 'Es necesario un nombre',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            FechaSolicitud::create([
                'nombre' => $request->input('nombre'),
                'anio' => $request->input('anio'),
                'semestre' => $request->input('semestre'),
                'fecha_inicio' => $request->input('fecha_inicio'),
                'fecha_fin' => $request->input('fecha_fin'),
            ]);
            return response()->json(['message' =>'Plazo para la recepción de solicutudes de cursos establecida']);
        } catch (Exception $e) {
            return response()->json(['error' =>$e->getMessage()], 500);
        }
    }

    public function comprobarFechaRecepcion(Request $request){
        $fechaActual = Carbon::now();
        $fechaSolicitud = FechaSolicitud::where('anio', $request->input('anio'))->where('semestre', $request->input('semestre'))->first();
        
        if(!$fechaSolicitud || !$fechaActual->between($fechaSolicitud->fecha_inicio, $fechaSolicitud->fecha_fin)){
            return response()->json(['error' => 'El periodo para realizar la solicitud de curso ha finalizado o no está disponible'], 400);
        }

        return response()->json(['messaje' => 'se puede hacer la solicitud'], 200);
    }
}