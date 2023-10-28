<?php

namespace App\Http\Controllers;

use App\Models\HorariosTrabajo;
use App\Models\Persona;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Trabajo;
use App\Models\Usuario;
use App\Models\Jornada;
use Illuminate\Support\Facades\Validator;


class TrabajoController extends Controller
{
    public function Agregar_trabajo(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'lugar_trabajo' => 'required',
                'cargo' => 'required',
                'jornada_id' => 'required| exists:jornadas,id',
                'horario' => 'required|array|min:1',
                'horario.*.dia_id' => 'required|exists:dias,id',
                'horario.*.horas' => 'required|array|min:1',
                'horario.*.horas.*.hora_inicio' => 'required',
                'horario.*.horas.*.hora_fin' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            //recuperamos el usuario al que se le agregara el trabajo
            $usuario = $request->user();
            // creamos el trabajo
            try {
                $nuevotrabajo = Trabajo::create([
                    'jornada_id' => $request->jornada_id,
                    'usuario_id' => $usuario->id,
                    'estado_id' => 5,
                    'lugar_trabajo' => $request->lugar_trabajo,
                    'cargo' => $request->cargo
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['message' => $e->getMessage()], 422);
            }
            // una vez creado el trabajo procedemos a añadir los dias 
            foreach ($request['horario'] as $dia) { // recorremos los dias que vienen del horario
                //recorremos las horas para agergar las horas que tiene el trabajo por dia
                foreach ($dia['horas'] as $horas) {
                    // creamos el el dia en referencia al horario trabajo 
                    try {
                        $diatrabajo = HorariosTrabajo::create([
                            'dia_id' => $dia['dia_id'],
                            'hora_inicio' => $horas['hora_inicio'],
                            'hora_fin' => $horas['hora_fin'],
                            'trabajo_id' => $nuevotrabajo->id
                        ]);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['message' => $e->getMessage()], 422);
                    }
                }
            }
            DB::commit();
            $perso = $this->Obtener_usuario_personaid($usuario->id);
            $jornada = Jornada::find($request->jornada_id);
            return response()->json([
                'message' => 'Se ha agregado el trabajo de manera exitosa',
                'persona' => $perso,
                'lugar' => $nuevotrabajo->lugar_trabajo,
                'cargo' => $nuevotrabajo->cargo,
                'jornada' => $jornada->sigla_jornada

            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }

    }
    public function Obtener_usuario_personaid($idusuario)
    {

        try {

            $usuario = Usuario::find($idusuario);
            $persona = Persona::where('id', $usuario->persona_id)->first();
            return ($persona->nombre);

        } catch (Exception $e) {
            throw $e;
        }
    }
}

