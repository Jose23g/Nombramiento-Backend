<?php

namespace App\Http\Controllers;

use App\Models\Dias;
use App\Models\HorariosTrabajo;
use App\Models\Jornada;
use App\Models\Persona;
use App\Models\Trabajo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrabajoController extends Controller
{
    public function agregue(Request $request)
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
            // recuperamos el usuario al que se le agregara el trabajo
            $usuario = $request->user();
            // creamos el trabajo
            try {
                $nuevotrabajo = Trabajo::create([
                    'jornada_id' => $request->jornada_id,
                    'usuario_id' => $usuario->id,
                    'estado_id' => 5,
                    'lugar_trabajo' => $request->lugar_trabajo,
                    'cargo' => $request->cargo,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json(['message' => $e->getMessage()], 422);
            }
            // una vez creado el trabajo procedemos a añadir los dias
            foreach ($request['horario'] as $dia) { // recorremos los dias que vienen del horario
                // recorremos las horas para agergar las horas que tiene el trabajo por dia
                foreach ($dia['horas'] as $horas) {
                    // creamos el el dia en referencia al horario trabajo
                    try {
                        $diatrabajo = HorariosTrabajo::create([
                            'dia_id' => $dia['dia_id'],
                            'hora_inicio' => $horas['hora_inicio'],
                            'hora_fin' => $horas['hora_fin'],
                            'trabajo_id' => $nuevotrabajo->id,
                        ]);
                    } catch (\Exception $e) {
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
                'jornada' => $jornada->sigla_jornada,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function Obtener_usuario_personaid($idusuario)
    {
        try {
            $usuario = Usuario::find($idusuario);
            $persona = Persona::where('id', $usuario->persona_id)->first();

            return $persona->nombre;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function obtengaElListadoPorPersona(Request $request)
    {
        $usuario = $request->user();

        try {
            $listadotrabajos = Trabajo::where('usuario_id', $usuario->id)->where('estado_id', 5)->get();

            if ($listadotrabajos->isEmpty()) {
                return response()->json(['message' => 'La persona no posee trabajos'], 200);
            }
            $trabajos_horarios = [];

            foreach ($listadotrabajos as $trabajo) {
                $diaslaborales = HorariosTrabajo::where('trabajo_id', $trabajo->id)->get();
                $jornada = Jornada::find($trabajo['jornada_id']);
                $horario = [];
                foreach ($diaslaborales as $dia) {
                    $nombredia = Dias::find($dia['dia_id']);

                    $lineadia = (object) [
                        'id' => $dia['id'],
                        'dia' => $nombredia->nombre,
                        'hora_inicio' => $dia['hora_inicio'],
                        'hora_fin' => $dia['hora_fin'],
                    ];
                    $horario[] = $lineadia;
                }
                $lineatrabajo = (object) [
                    'id' => $trabajo['id'],
                    'Lugar' => $trabajo['lugar_trabajo'],
                    'Cargo' => $trabajo['cargo'],
                    'jornada' => $jornada->sigla_jornada,
                    'horas_jornada' => $jornada->horas_jornada,
                    'horario_trabajo' => $horario,
                ];

                $trabajos_horarios[] = $lineatrabajo;
            }

            return response()->json(['Listado_trabajos' => $trabajos_horarios], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function modifique(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:trabajos,id',
            'cargo' => 'required',
            'horario' => 'required|array|min:1',
            'horario.*.dia_id' => 'required|exists:dias,id',
            'horario.*.horas' => 'required|array|min:1',
            'horario.*.horas.*.hora_inicio' => 'required',
            'horario.*.horas.*.hora_fin' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            // buscamos el trabajo
            $trabajoactualizar = Trabajo::find($request->id);
            // actualizamos su cargo el unico espacio a modificar
            $trabajoactualizar->cargo = $request->cargo;
            $trabajoactualizar->save();
            // ahora eliminamos las referencias de los dias de trabajo del mismo
            HorariosTrabajo::where('trabajo_id', $trabajoactualizar->id)->delete();
            foreach ($request['horario'] as $dias) {
                // recorremos los dias para agregar
                foreach ($dias['horas'] as $hora) {
                    try {
                        $diatrabajo = HorariosTrabajo::create([
                            'dia_id' => $dias['dia_id'],
                            'hora_inicio' => $hora['hora_inicio'],
                            'hora_fin' => $hora['hora_fin'],
                            'trabajo_id' => $trabajoactualizar->id,
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();

                        return response()->json(['message' => $e->getMessage()], 422);
                    }
                }
            }
            DB::commit();
            $jornada = Jornada::find($trabajoactualizar->jornada_id);

            return response()->json([
                'message' => 'Se ha actualizado el trabajo con exito',
                'id' => $trabajoactualizar->id,
                'lugar' => $trabajoactualizar->lugar_trabajo,
                'cargo' => $trabajoactualizar->cargo,
                'jornada' => $jornada->sigla_jornada,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function elimine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:trabajos,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $trabajoeliminar = Trabajo::find($request->id);
            $trabajoeliminar->estado_id = 6;
            $trabajoeliminar->save();

            $jornada = Jornada::find($trabajoeliminar->jornada_id);

            return response()->json([
                'message' => 'Se ha actualizado el eliminado el trabajo con exito',
                'id' => $trabajoeliminar->id,
                'lugar' => $trabajoeliminar->lugar_trabajo,
                'cargo' => $trabajoeliminar->cargo,
                'jornada' => $jornada->sigla_jornada,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function obtengaPorId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:trabajos,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $trabajo = Trabajo::with('jornada')->find($request->id);
            $horariotrabajo = HorariosTrabajo::where('trabajo_id', $trabajo->id)->get();
            $horario = [];
            foreach ($horariotrabajo as $dia) {
                $nombredia = Dias::find($dia['dia_id']);

                $lineadia = (object) [
                    'id' => $dia['id'],
                    'dia' => $nombredia->nombre,
                    'hora_inicio' => $dia['hora_inicio'],
                    'hora_fin' => $dia['hora_fin'],
                ];
                $horario[] = $lineadia;
            }

            $jornada = Jornada::find($trabajo->jornada_id);

            return response()->json([
                'message' => 'Se ha encontrado el trabajo solicitado',
                'id' => $trabajo->id,
                'lugar' => $trabajo->lugar_trabajo,
                'cargo' => $trabajo->cargo,
                'jornada' => $jornada->sigla_jornada,
                'horario' => $horario,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
