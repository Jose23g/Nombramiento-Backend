<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\PlanEstudios;
use App\Models\CursoPlan;
use Exception;
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
            'grado_anual' => 'required',
            'ciclo' => 'required',
            'horas_teoricas' => 'required',
            'individual_colegiado' => 'required',
            'tutoria' => 'required',
            'horas_practicas' => 'required',
            'horas_laboratorio' => 'required',
            'planes' => 'required',

        ], [
            'required' => 'El campo :attribute es requerido.',
            'unique' => 'Ya existe un valor en la columna :attribute similar al ingresado.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        try {
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

            if($nuevocurso){
              
                foreach( $request->planes as $plan ){
                    $relacioncursoplan = CursoPlan::create([
                        'curso_id' => $nuevocurso->id,
                        'plan_estudios_id' => $plan['id']
                    ]);
                }
            }
            return response()->json(['message' => 'Se ha registrado el curso con exito el curso' . $nuevocurso->nombre], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }

    }

    public function cursosCarrera(Request $request)
    {

        $carrera = $request->user()->carreras->first();
        $planes = PlanEstudios::where('carrera_id', $carrera->id)->get();
        $listadecursos = [];

        foreach ($planes as $plan) {
            $planbuscado = PlanEstudios::find($plan->id);

            $cursos = $planbuscado->cursos;

            foreach($cursos as $curso){

    
                $listadecursos[] = (object)[
                    'id'=>  $curso->id,
                    'plan_estudios' => $planbuscado->anio . ' - ' . $planbuscado->grado->nombre,
                    'sigla' => $curso->sigla,
                    'nombre' => $curso->nombre,
                    'creditos' => $curso->creditos,
                    'ciclos' => $curso->ciclo,
                    'grado_anual' => $curso->grado_anual,
                    'horas_teoricas' => $curso->horas_teoricas,
                    'horas_practicas' => $curso->horas_practicas,
                    'horas_laboratorio' => $curso->horas_laboratorio
                ];
            }
            
        }
        return response()->json($listadecursos);
    }
}
