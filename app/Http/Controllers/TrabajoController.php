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

    public function Eliminar_proyectos_accion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            $trabajoeliminar = Actividad::find($request->id);
            if ($trabajoeliminar->categoria !== "proyecto_investigacion_accion_social") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }
            // luego se elimna de manera total
            $trabajoeliminar->estado_id = $this->obtenerestado('inactivo');
            $trabajoeliminar->save();

            $carga = Carga::find($trabajoeliminar->carga_id);

            return response()->json([
                'message' => 'se ha eliminado con exito el proyecto I/A',
                'id' => $trabajoeliminar->id,
                'numero' => $trabajoeliminar->numero_oficio,
                'nombre' => $trabajoeliminar->nombre,
                'carga' => $carga->nombre,
                'vigencia' => $trabajoeliminar->fecha_inicio . '/' . $trabajoeliminar->fecha_fin,
            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);

        }

    }
    public function Buscar_proyectos_accion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            $actividadbuscada = Actividad::find($request->id);

            if ($actividadbuscada->categoria !== "proyecto_investigacion_accion_social") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            $carga = Carga::find($actividadbuscada->carga_id);
            return response()->json([
                'message' => ' El proyecto I/A  consultado es',
                'id' => $actividadbuscada->id,
                'numero' => $actividadbuscada->numero_oficio,
                'nombre' => $actividadbuscada->nombre,
                'carga' => $carga->nombre,
                'fecha_inicio' => $actividadbuscada->fecha_inicio,
                'fecha_fin' => $actividadbuscada->fecha_fin,

            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $validator->errors()], 422);

        }
    }

    // Cargos docente, administrativos, comisiones
    public function Agregar_cargo_DAC(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cargo_comision' => 'required',
            'numero_oficio' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'carga_id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()]);
        }

        DB::beginTransaction();

        try {
            $usuario = $request->user();

            $nuevaactividad = Actividad::create([
                'categoria' => 'cargo_dac',
                'numero_oficio' => $request->numero_oficio,
                'cargo_comision' => $request->cargo_comision,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'carga_id' => $request->carga_id,
                'estado_id' => $this->obtenerestado('activo'),
                'usuario_id' => $usuario->id,
            ]);

            DB::commit();

            $carga = Carga::find($request->carga_id);

            return response()->json([
                'message' => 'se ha agregado el cargo DAC',
                'numero_oficio' => $nuevaactividad->numero_oficio,
                'cargo_comision' => $nuevaactividad->cargo_comision,
                'vigencia' => $nuevaactividad->fecha_inicio . ' / ' . $nuevaactividad->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $validator->errors()], 422);
        }

    }
    public function Listar_cargos_DAC(Request $request)
    {
        $usuario = $request->user();

        try {
            $estado = $this->obtenerestado('activo');
            $cargosdac = Actividad::where('usuario_id', $usuario->id)->where('categoria', 'cargo_dac')->where('estado_id', $estado)->get();

            if ($cargosdac->isEmpty()) {

                return response()->json(['message' => 'no se encuentran cargos DAC'], 200);
            }

            $listaactividades = [];

            foreach ($cargosdac as $actividad) {

                $carga = Carga::find($actividad['carga_id']);

                $lineaactividad = (object) [
                    'id' => $actividad['id'],
                    'numero_oficio' => $actividad['numero_oficio'],
                    'cargo_comision' => $actividad['cargo_comision'],
                    'vigencia' => $actividad['fecha_inicio'] . '/' . $actividad['fecha_fin'],
                    'carga' => $carga->nombre,
                ];
                $listaactividades[] = $lineaactividad;
            }

            return response()->json(['DAC' => $listaactividades], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Editar_cargo_DAC(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
            'numero_oficio' => 'required',
            'cargo_comision' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $actividadeditar = Actividad::find($request->id);

            if ($actividadeditar->categoria !== "cargo_dac") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            $actividadeditar->numero_oficio = $request->numero_oficio;
            $actividadeditar->cargo_comision = $request->cargo_comision;
            $actividadeditar->fecha_inicio = $request->fecha_inicio;
            $actividadeditar->fecha_fin = $request->fecha_fin;
            $actividadeditar->save();

            $carga = Carga::find($actividadeditar->carga_id);

            return response()->json([
                'message' => 'se ha editado el cargo DAC',
                'id' => $actividadeditar->id,
                'numero_oficio' => $actividadeditar->numero_oficio,
                'cargo_comision' => $actividadeditar->cargo_comision,
                'vigencia' => $actividadeditar->fecha_inicio . '/' . $actividadeditar->fecha_fin,
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Eliminar_cargo_DAC(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $actividadeliminar = Actividad::find($request->id);
            if ($actividadeliminar->categoria !== "cargo_dac") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            $actividadeliminar->estado_id = $this->obtenerestado('inactivo');
            $actividadeliminar->save();

            $carga = Carga::find($actividadeliminar->carga_id);

            return response()->json([
                'message' => 'se ha eliminado con exito el proyecto de accion social',
                'id' => $actividadeliminar->id,
                'numero_oficio' => $actividadeliminar->numero_oficio,
                'cargo_comision' => $actividadeliminar->cargo_comision,
                'vigencia' => $actividadeliminar->fecha_inicio . '/' . $actividadeliminar->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Buscar_cargo_DAC(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            $actividadbuscada = Actividad::find($request->id);

            if ($actividadbuscada->categoria !== "cargo_dac") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            $carga = Carga::find($actividadbuscada->carga_id);

            return response()->json([
                'message' => ' El proyecto DAC consultado es',
                'id' => $actividadbuscada->id,
                'numero_oficio' => $actividadbuscada->numero_oficio,
                'cargo_comision' => $actividadbuscada->cargo_comision,
                'carga' => $carga->nombre,
                'fecha_inicio' => $actividadbuscada->fecha_inicio,
                'fecha_fin' => $actividadbuscada->fecha_fin,
            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $validator->errors()], 422);

        }
    }

    // METODO PARA otras labores no contempladas

    public function Agregar_otra_labor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero' => 'required',
            'nombre' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'carga_id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()]);
        }

        DB::beginTransaction();

        try {
            $usuario = $request->user();

            $nuevaactividad = Actividad::create([
                'categoria' => 'otra_labor',
                'numero_oficio' => $request->numero,
                'nombre' => $request->nombre,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'carga_id' => $request->carga_id,
                'estado_id' => $this->obtenerestado('activo'),
                'usuario_id' => $usuario->id,
            ]);

            DB::commit();

            $carga = Carga::find($request->carga_id);

            return response()->json([
                'message' => 'se ha agregado otra labor',
                'numero' => $nuevaactividad->numero_oficio,
                'nombre' => $nuevaactividad->nombre,
                'vigencia' => $nuevaactividad->fecha_inicio . ' / ' . $nuevaactividad->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $validator->errors()], 422);
        }
    }
    public function Listar_otras_labores(Request $request)
    {
        $usuario = $request->user();

        try {
            $estado = $this->obtenerestado('activo');
            $otraslabores = Actividad::where('usuario_id', $usuario->id)->where('categoria', 'otra_labor')->where('estado_id', $estado)->get();

            if ($otraslabores->isEmpty()) {

                return response()->json(['message' => 'no se encuentran otras labores'], 200);
            }

            $listaactividades = [];

            foreach ($otraslabores as $actividad) {

                $carga = Carga::find($actividad['carga_id']);

                $lineaactividad = (object) [
                    'id' => $actividad['id'],
                    'numero' => $actividad['numero_oficio'],
                    'nombre' => $actividad['nombre'],
                    'vigencia' => $actividad['fecha_inicio'] . '/' . $actividad['fecha_fin'],
                    'carga' => $carga->nombre,
                ];

                $listaactividades[] = $lineaactividad;
            }

            return response()->json(['OTL' => $listaactividades], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Editar_otra_labor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
            'numero' => 'required',
            'nombre' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $actividadeditar = Actividad::find($request->id);

            if ($actividadeditar->categoria !== "otra_labor") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            $actividadeditar->numero_oficio = $request->numero;
            $actividadeditar->nombre = $request->nombre;
            $actividadeditar->fecha_inicio = $request->fecha_inicio;
            $actividadeditar->fecha_fin = $request->fecha_fin;
            $actividadeditar->save();
            $carga = Carga::find($actividadeditar->carga_id);

            return response()->json([
                'message' => 'se ha editado con exito otra labor',
                'id' => $actividadeditar->id,
                'numero' => $actividadeditar->numero_oficio,
                'nombre' => $actividadeditar->nombre,
                'vigencia' => $actividadeditar->fecha_inicio . '/' . $actividadeditar->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Eliminar_otra_labor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            $trabajoeliminar = Actividad::find($request->id);

            if ($trabajoeliminar->categoria !== "otra_labor") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            // luego se elimna de manera total
            $trabajoeliminar->estado_id = $this->obtenerestado('inactivo');
            $trabajoeliminar->save();

            $carga = Carga::find($trabajoeliminar->carga_id);

            return response()->json([
                'message' => 'se ha eliminado con exito el proyecto I/A',
                'id' => $trabajoeliminar->id,
                'numero' => $trabajoeliminar->numero_oficio,
                'nombre' => $trabajoeliminar->nombre,
                'carga' => $carga->nombre,
                'vigencia' => $trabajoeliminar->fecha_inicio . '/' . $trabajoeliminar->fecha_fin,
            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);

        }
    }
    public function Buscar_otra_labor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            $actividadbuscada = Actividad::find($request->id);

            if ($actividadbuscada->categoria !== "otra_labor") {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            $carga = Carga::find($actividadbuscada->carga_id);

            return response()->json([
                'message' => ' otra labor consultada es',
                'id' => $actividadbuscada->id,
                'numero' => $actividadbuscada->numero_oficio,
                'nombre' => $actividadbuscada->nombre,
                'carga' => $carga->nombre,
                'fecha_inicio' => $actividadbuscada->fecha_inicio,
                'fecha_fin' => $actividadbuscada->fecha_fin,

            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $validator->errors()], 422);

        }
    }

    // METODO PARA OBTENER TODAS LAS OTRAS ACTIVIDADES DE UN USUARIO PARA P6
    public function Disparador($usuarioID)
    {

        try {
            $fechaparametro = Carbon::now()->format('Y-m-d');

            $actividadesTFG = $this->Cargar_actividad_categoria('trabajo_final_graduacion', $usuarioID, $fechaparametro);

            $actividadesPIAC = $this->Cargar_actividad_categoria('proyecto_investigacion_accion_social', $usuarioID, $fechaparametro);

            $actividadesDAC = $this->Cargar_actividad_categoria('cargo_dac', $usuarioID, $fechaparametro);

            $actividadesOTRA = $this->Cargar_actividad_categoria('otra_labor', $usuarioID, $fechaparametro);

            $listaactividades = (object) [
                'TFG' => $actividadesTFG,
                'PIAC' => $actividadesPIAC,
                'DAC' => $actividadesDAC,
                'OTRA' => $actividadesOTRA,
            ];

            return $listaactividades;

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);

        }

    }

    public function Cargar_actividad_categoria($categoria, $id_usuario, $fechaparametro)
    {

        $actividadenvigencia = Actividad::where('fecha_inicio', '<=', $fechaparametro)
            ->where('fecha_fin', '>=', $fechaparametro)->where('categoria', $categoria)
            ->where('usuario_id', $id_usuario)->where('estado_id', $this->obtenerestado('activo'))
            ->get();

        if (!$actividadenvigencia->isEmpty()) {

            $listadoactividad = [];

            switch ($categoria) {

                case "trabajo_final_graduacion":

                    foreach ($actividadenvigencia as $actividad) {

                        $carga = Carga::find($actividad['carga_id']);

                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'tipo' => $actividad['tipo'],
                            'estudiante' => $actividad['estudiante'],
                            'modalidad' => $actividad['modalidad'],
                            'grado' => $actividad['grado'],
                            'postgrado' => $actividad['postgrado'],
                            'vigencia' => $actividad['fecha_inicio'] . '/' . $actividad['fecha_fin'],
                            'carga' => $carga->nombre,
                        ];

                        $listadoactividad[] = $lineaactividad;
                    }

                    return $listadoactividad;

                    break;

                case "proyecto_investigacion_accion_social":

                    foreach ($actividadenvigencia as $actividad) {

                        $carga = Carga::find($actividad['carga_id']);

                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'numero' => $actividad['numero_oficio'],
                            'nombre' => $actividad['nombre'],
                            'vigencia' => $actividad['fecha_inicio'] . '/' . $actividad['fecha_fin'],
                            'carga' => $carga->nombre,
                        ];

                        $listadoactividad[] = $lineaactividad;
                    }
                    return $listadoactividad;

                    break;

                case "cargo_dac":

                    foreach ($actividadenvigencia as $actividad) {
                        $carga = Carga::find($actividad['carga_id']);
                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'numero_oficio' => $actividad['numero_oficio'],
                            'cargo_comision' => $actividad['cargo_comision'],
                            'vigencia' => $actividad['fecha_inicio'] . '/' . $actividad['fecha_fin'],
                            'carga' => $carga->nombre,
                        ];

                        $listadoactividad[] = $lineaactividad;
                    }
                    return $listadoactividad;
                    break;

                case "otra_labor":
                    foreach ($actividadenvigencia as $actividad) {
                        $carga = Carga::find($actividad['carga_id']);

                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'numero' => $actividad['numero_oficio'],
                            'nombre' => $actividad['nombre'],
                            'vigencia' => $actividad['fecha_inicio'] . '/' . $actividad['fecha_fin'],
                            'carga' => $carga->nombre,
                        ];

                        $listadoactividad[] = $lineaactividad;
                    }
                    return $listadoactividad;

                    break;

                default:
            }
        } else {
            return null;
        }
    }

}
