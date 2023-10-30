<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\HorariosTrabajo;
use App\Models\Persona;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Trabajo;
use App\Models\Usuario;
use App\Models\Carga;
use App\Models\Dias;
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
    public function Obtener_listado_trabajos_Persona(Request $request)
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
                        'hora_fin' => $dia['hora_fin']
                    ];
                    $horario[] = $lineadia;
                }
                $lineatrabajo = (object) [
                    'id' => $trabajo['id'],
                    'Lugar' => $trabajo['lugar_trabajo'],
                    'Cargo' => $trabajo['cargo'],
                    'jornada' => $jornada->sigla_jornada,
                    'horas_jornada' => $jornada->horas_jornada,
                    'horario_trabajo' => $horario
                ];

                $trabajos_horarios[] = $lineatrabajo;
            }

            return response()->json(['Listado_trabajos' => $trabajos_horarios], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }


    }

    public function Editar_trabajo(Request $request)
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
            //buscamos el trabajo
            $trabajoactualizar = Trabajo::find($request->id);
            //actualizamos su cargo el unico espacio a modificar
            $trabajoactualizar->cargo = $request->cargo;
            $trabajoactualizar->save();
            //ahora eliminamos las referencias de los dias de trabajo del mismo
            HorariosTrabajo::where('trabajo_id', $trabajoactualizar->id)->delete();
            foreach ($request['horario'] as $dias) {
                //recorremos los dias para agregar
                foreach ($dias['horas'] as $hora) {

                    try {

                        $diatrabajo = HorariosTrabajo::create([
                            'dia_id' => $dias['dia_id'],
                            'hora_inicio' => $hora['hora_inicio'],
                            'hora_fin' => $hora['hora_fin'],
                            'trabajo_id' => $trabajoactualizar->id
                        ]);
                    } catch (Exception $e) {
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

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Eliminar_trabajo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:trabajos,id'
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

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }

    }
    public function Buscar_trabajo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:trabajos,id'
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
                    'hora_fin' => $dia['hora_fin']
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
                'horario' => $horario
            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    // TRABAJOS FINALES DE GRADUACION P6
    public function Agregar_trabajofinal_graduacion(Request $request)
    {

        $Validator = Validator::make($request->all(), [
            'tipo' => 'required',
            'estudiante' => 'required',
            'modalidad' => 'required',
            'grado' => 'required',
            'postgrado' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'carga_id' => 'required|exists:cargas,id',
        ]);

        if ($Validator->fails()) {

            return response()->json(['error' => $Validator->errors()]);
        }
        try {

            $usuario = $request->user();

            $carga = Carga::find($request->carga_id);

            $nuevaactividad = Actividad::create([
                'categoria' => "trabajofinalgraduacion",
                'tipo' => $request->tipo,
                'estudiante' => $request->estudiante,
                'modalidad' => $request->modalidad,
                'grado' => $request->grado,
                'postgrado' => $request->postgrado,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'carga_id' => $request->carga_id,
                'usuario_id' => $usuario->id,
                'estado_id' => 5
            ]);

            return response()->json([
                'message' => 'Se ha inresado Trabajo Final de Graduacion',
                'tipo' => $nuevaactividad->tipo,
                'estudiante' => $nuevaactividad->estudiante,
                'modalidad' => $nuevaactividad->modalidad,
                'vigencia' => $nuevaactividad->fecha_inicio . ' / ' . $nuevaactividad->fecha_fin,
                'carga' => $carga->nombre
            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }

    }
    public function Listar_trabajosfinal_graduacion(Request $request)
    {
        $usuario = $request->user();

        try {

            $actividades = Actividad::where('usuario_id', $usuario->id)->where('categoria', 'trabajofinalgraduacion')->where('estado_id', 5)->get();
            
            if($actividades->isEmpty()){

                return response()->json(['message' => 'no se encuentran trabajos de graduacion activos'], 200);
            }

            $listaactividades = [];

            foreach ($actividades as $actividad) {

                $carga = Carga::find($actividad['carga_id']);

                $lineaactividad = (object) [
                    'id' => $actividad['id'],
                    'tipo' => $actividad['tipo'],
                    'estudiante' => $actividad->estudiante,
                    'modalidad' => $actividad['modalidad'],
                    'grado' => $actividad['grado'],
                    'postgrado' => $actividad['postgrado'],
                    'vigencia' => $actividad['fecha_inicio'] . '/' . $actividad['fecha_fin'],
                    'carga' => $carga->nombre
                ];
                $listaactividades[] = $lineaactividad;
            }

            return response()->json(['TFG' => $listaactividades], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Editar_trabajofinal_graduacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
            'estudiante' => 'required',
            'modalidad' => 'required',
            'grado' => 'required',
            'postgrado' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            $trabajofinaleditar = Actividad::find($request->id);
            $trabajofinaleditar->estudiante = $request->estudiante;
            $trabajofinaleditar->modalidad = $request->modalidad;
            $trabajofinaleditar->grado = $request->grado;
            $trabajofinaleditar->postgrado = $request->postgrado;
            $trabajofinaleditar->fecha_inicio = $request->fecha_inicio;
            $trabajofinaleditar->fecha_fin = $request->fecha_fin;
            $trabajofinaleditar->save();

            $carga = Carga::find($trabajofinaleditar->carga_id);

            return response()->json([
                'message' => 'se ha editado con exito el trabajo final de graduacion',
                'id' => $trabajofinaleditar->id,
                'tipo' => $trabajofinaleditar->tipo,
                'estudiante' =>$trabajofinaleditar->estudiante,
                'modalidad' => $trabajofinaleditar->modalidad,
                'grado' => $trabajofinaleditar->grado,
                'postgrado' => $trabajofinaleditar->postgrado,
                'carga' => $carga->nombre,
                'vigencia' => $trabajofinaleditar->fecha_inicio . '/' . $trabajofinaleditar->fecha_fin,

            ], 200);

        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    public function Eliminar_trabajofinal_graduacion(Request $request)
    {
        $validator =  Validator::make($request->all(),[
            'id' => 'required|exists:actividades,id'
        ]);

        if($validator->fails()) {
            return response()->json(['error'=> $validator->errors()],422);  
        }

        try {
            
            $actividadeliminar = Actividad::find($request->id);
            //pendiente el metodo eliminar real
            $actividadeliminar->estado_id = 6;
            $actividadeliminar->save();

            $carga = Carga::find($actividadeliminar->carga_id);

            return response()->json([
                'message' => 'se ha eliminado con exito el trabajo final de graduacion',
                'id' => $actividadeliminar->id,
                'tipo' => $actividadeliminar->tipo,
                'estudiante' =>$actividadeliminar->estudiante,
                'modalidad' => $actividadeliminar->modalidad,
                'grado' => $actividadeliminar->grado,
                'postgrado' => $actividadeliminar->postgrado,
                'carga' => $carga->nombre,
                'vigencia' => $actividadeliminar->fecha_inicio . '/' . $actividadeliminar->fecha_fin,

            ], 200);
            
        }catch(Exception $e){

            return response()->json(['error'=> $validator->errors()],422);

        }
    }

    public function Buscar_trabajofinal_graduacion(Request $request){

        $validator =  Validator::make($request->all(),[
            'id' => 'required|exists:actividades,id'
        ]);

        if($validator->fails()) {
            return response()->json(['error'=> $validator->errors()],422);  
        }

        try {
            
            $actividadbuscada = Actividad::find($request->id);
            $carga = Carga::find($actividadbuscada->carga_id);
            return response()->json([
                'message' => ' El trabajo final de graduacion consultado es',
                'id' => $actividadbuscada->id,
                'tipo' => $actividadbuscada->tipo,
                'estudiante' =>$actividadbuscada->estudiante,
                'modalidad' => $actividadbuscada->modalidad,
                'grado' => $actividadbuscada->grado,
                'postgrado' => $actividadbuscada->postgrado,
                'carga' => $carga->nombre,
                'vigencia' => $actividadbuscada->fecha_inicio . '/' . $actividadbuscada->fecha_fin,

            ], 200);
            
        }catch(Exception $e){

            return response()->json(['error'=> $validator->errors()],422);

        }
    }


    //Proyectos de investigacion Accionsocial P6

    public function Agregar_proyecto_accion(Request $request)
    {

    }
    public function Listar_proyectos_accion(Request $request)
    {

    }
    public function Editar_proyectos_accion(Request $request)
    {

    }
    public function Eliminar_proyectos_accion(Request $request)
    {

    }

    // Cargos docente, administrativos, comisiones 
    public function Agregar_cargo_DAC(Request $request)
    {

    }
    public function Listar__cargos_DAC(Request $request)
    {

    }
    public function Editar__cargo_DAC(Request $request)
    {

    }
    public function Eliminar__cargo_DAC(Request $request)
    {

    }
    // METODO PARA OBTENER TODAS LAS OTRAS ACTIVIDADES DE UN USUARIO PARA P6

    public function Obtener_trabajo_actividades_persona(Request $request)
    {

    }

}




