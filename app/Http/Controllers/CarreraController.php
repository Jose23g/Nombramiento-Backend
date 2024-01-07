<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

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
    public function listar_Carreras (Request $request){
        $listadocarreras = Carrera::select('id', 'nombre')->get();
        return $listadocarreras;
    }
    public function editar_Carrera (Request $request){
        
        $validator = Validator::make($request->all(),[
            'carrera_id' => 'required|exists:carreras,id',
            'nombre' => [
                'required',
                Rule::unique('carreras', 'nombre')->ignore($request->carrera_id),
            ]
        ],[
            'exists' => 'error referencia no encontrada',
            'unique' =>'nombre ya en uso'
        ]);

        if($validator->fails()) {
            return response()->json([$validator->errors()],422);
        }

        try{
             $carreraeditar = Carrera::find($request->carrera_id); 
             $carreraeditar->nombre = $request->nombre;
             $carreraeditar->save();
              return response()->json(['message'=> $carreraeditar]);

        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()],500);
        }
    }
    public function Agregar_Carrera (Request $request){
        
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|unique:carreras,nombre',
        ],[
            'unique' => 'Nombre ya en uso'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()],422);
        }

        try{
              $nuevacarrera = Carrera::create([
                'nombre' => $request->nombre
              ]);  

              return response()->json(['message'=> $nuevacarrera]);

        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()],500);
        }
    }
}
