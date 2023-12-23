<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\Dias;
use App\Models\Fecha;
use App\Models\HorariosTrabajo;
use App\Models\Jornada;
use App\Models\Persona;
use App\Models\Trabajo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrabajoController extends Controller
{
    public function obtengaElListadoDeTrabajosExternos(Request $request)
    {
        $trabajos = $request->user()->trabajos()->with(['fecha', 'horarioTrabajos.dia'])->where('tipo_id', 3)->where('estado_id', 5)->get();

        return response()->json($trabajos->filter(function ($trabajo) {
            $fechaFin = Carbon::parse($trabajo->fecha->fecha_fin);
            $fechaInicio = Carbon::parse($trabajo->fecha->fecha_inicio);
            $fechaActual = Carbon::now();

            return $fechaActual->between($fechaInicio, $fechaFin);
        })->values()->toArray());
    }
    public function obtengaElListadoDeTrabajosInternos(Request $request)
    {
        $listadoDeTrabajos = $request->user()->trabajos()->with(['fecha', 'horarioTrabajos.dia'])->where('tipo_id', 2)->where('estado_id', 5)->get();
        $trabajos = $listadoDeTrabajos->filter(function ($trabajo) {
            $fechaFin = Carbon::parse($trabajo->fecha->fecha_fin)->subMonth(4);
            $fechaActual = Carbon::now();

            return $fechaActual < $fechaFin;
        });
        if ($trabajos->isEmpty()) {
            $trabajos = $listadoDeTrabajos->filter(function ($trabajo) {
                $fechaFin = Carbon::parse($trabajo->fecha->fecha_fin);
                $fechaInicio = Carbon::parse($trabajo->fecha->fecha_inicio);
                $fechaActual = Carbon::now();

                return $fechaActual->subYear()->isBetween($fechaInicio->subMonth(5), $fechaFin->subMonth(4));
            });
        }
        $total_horas = 0;
        $trabajosConElTotalDeHoras = new \stdClass();
        $trabajosConElTotalDeHoras->trabajos = $trabajos->values()->map(function ($trabajo) use (&$total_horas) {
            $total_horas += app(SolicitudGrupoController::class)->obtengaElValor($trabajo->jornada);
            return $trabajo;
        });
        $trabajosConElTotalDeHoras->total_horas = $total_horas * 40;
        return response()->json($trabajosConElTotalDeHoras);
    }
    public function agregueUnTrabajoInterno($request)
    {

        $fecha = app(FechaController::class)->agregueParaUnTrabajoInterno(['fecha_inicio' => $request['fecha']['fecha_inicio'], 'fecha_fin' => $request['fecha']['fecha_fin']]);
        if ($fecha) {
            $trabajo = Trabajo::create([
                'lugar_trabajo' => $request['lugar_trabajo'],
                'cargo_categoria' => $request['cargo_categoria'],
                'jornada' => $request['jornada'],
                'usuario_id' => $request['user_id'],
                'tipo_id' => 2,
                'estado_id' => 5,
                'fecha_id' => $fecha->id,
            ]);
            if ($trabajo) {
                app(HorariosTrabajoController::class)->agregue(['trabajo_id' => $trabajo->id, 'horarios' => $request['horario_trabajos']]);
            }
            return $trabajo;
        }

    }
    public function modifiqueUnTrabajoInterno(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required',
                'lugar_trabajo' => 'required',
                'cargo_categoria' => 'required',
                'jornada' => 'required',
                'fecha_id' => 'required|exists:fechas,id',
                'fecha_inicio' => 'required',
                'fecha_fin' => 'required',
                'horario_trabajos' => 'required|array|min:1',
                'horario_trabajos.*.dia_id' => 'required|exists:dias,id',
                'horario_trabajos.*.hora_inicio' => 'required',
                'horario_trabajos.*.hora_fin' => 'required',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            app(FechaController::class)->modifique(['id' => $request->fecha_id, 'fecha_inicio' => $request->fecha_inicio, 'fecha_fin' => $request->fecha_fin]);
            Trabajo::find($request->id)->update([
                'lugar_trabajo' => $request->lugar_trabajo,
                'cargo_categoria' => $request->cargo_categoria,
                'jornada' => $request->jornada,
            ]);
            app(HorariosTrabajoController::class)->agregue(['trabajo_id' => $request->id, 'horarios' => $request->horario_trabajos]);
            DB::commit();
            return response()->json(['Message' => 'Se ha registrado con éxito'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        }

    }
    public function elimineUnTrabajoInterno(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $trabajo = Trabajo::find($request->id)->delete();
        return response()->json(['message' => 'Se ha eliminado exitosamente', 'trabajo' => $trabajo], 200);
    }
    public function agregue(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'lugar_trabajo' => 'required',
                'cargo' => 'required',
                'jornada' => 'required',
                'horario' => 'required|array|min:1',
                'horario.*.dia_id' => 'required',
                'horario.*.desde' => 'required',
                'horario.*.hasta' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        
        DB::beginTransaction();
        try {
            // recuperamos el usuario al que se le agregara el trabajo
            $usuario = $request->user();
            $jornada = Carga::find($request->jornada);
            // creamos el trabajo
            try {

                $nuevafecha =  Fecha::create([
                    'tipo_id' => 5,
                    'fecha_inicio' => $request->fecha_inicio,
                    'fecha_fin' => $request->fecha_fin,
                ]);

                $nuevotrabajo = Trabajo::create([
                    'jornada' => $jornada->nombre,
                    'usuario_id' => $usuario->id,
                    'estado_id' => 5,
                    'tipo_id' => 5,
                    'lugar_trabajo' => $request->lugar_trabajo,
                    'cargo_categoria' => $request->cargo,
                    'fecha_id' => $nuevafecha->id
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json(['message' => $e->getMessage()], 422);
            }
           
            foreach ($request['horario'] as $dia) { // recorremos los dias que vienen del horario
                try {
                    $id_dia = Dias::where('nombre', $dia['dia_id'])->first();
                
                    $diatrabajo = HorariosTrabajo::create([
                        'dia_id' => $id_dia->id,
                        'hora_inicio' => $dia['desde'],
                        'hora_fin' => $dia['hasta'],
                        'trabajo_id' => $nuevotrabajo->id,
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();

                    return response()->json(['message' => $e->getMessage()], 422);
                }

            }
            DB::commit();
            $perso = $this->Obtener_usuario_personaid($usuario->id);

            return response()->json([
                'message' => 'Se ha agregado el trabajo de manera exitosa',
                'persona' => $perso,
                'lugar' => $nuevotrabajo->lugar_trabajo,
                'cargo' => $nuevotrabajo->cargo
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
                $fecha = Fecha::where('id',$trabajo['fecha_id'])->first();
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
                    'Cargo' => $trabajo['cargo_categoria'],
                    'jornada' => $trabajo['jornada'],
                    'fecha_inicio' => $fecha->fecha_inicio,
                    'fecha_fin' => $fecha->fecha_inicio,
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
            $trabajo = Trabajo::find($request->id);
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
