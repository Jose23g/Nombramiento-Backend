<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Carga;
use App\Models\Estado;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ActividadController extends Controller
{
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
                'categoria' => 'trabajo_final_graduacion',
                'tipo' => $request->tipo,
                'estudiante' => $request->estudiante,
                'modalidad' => $request->modalidad,
                'grado' => $request->grado,
                'postgrado' => $request->postgrado,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'carga_id' => $request->carga_id,
                'usuario_id' => $usuario->id,
                'estado_id' => $this->obtenerestado('activo'),
            ]);

            return response()->json([
                'message' => 'Se ha inresado Trabajo Final de Graduacion',
                'tipo' => $nuevaactividad->tipo,
                'estudiante' => $nuevaactividad->estudiante,
                'modalidad' => $nuevaactividad->modalidad,
                'vigencia' => $nuevaactividad->fecha_inicio.' / '.$nuevaactividad->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function Listar_trabajosfinal_graduacion(Request $request)
    {
        $usuario = $request->user();

        try {
            $actividades = Actividad::where('usuario_id', $usuario->id)->where('categoria', 'trabajo_final_graduacion')->where('estado_id', $this->obtenerestado('activo'))->get();

            if ($actividades->isEmpty()) {
                return response()->json(['message' => 'no se encuentran trabajos de graduacion activos'], 200);
            }

            $listaactividades = [];

            foreach ($actividades as $actividad) {
                $carga = Carga::find($actividad['carga_id']);

                $lineaactividad = (object) [
                    'id' => $actividad['id'],
                    'tipo' => $actividad['tipo'],
                    'estudiante' => $actividad['estudiante'],
                    'modalidad' => $actividad['modalidad'],
                    'grado' => $actividad['grado'],
                    'postgrado' => $actividad['postgrado'],
                    'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
                    'carga' => $carga->nombre,
                ];
                $listaactividades[] = $lineaactividad;
            }

            return response()->json(['TFG' => $listaactividades], 200);
        } catch (\Exception $e) {
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
            'fecha_fin' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $trabajofinaleditar = Actividad::find($request->id);

            if ($trabajofinaleditar->categoria !== 'trabajo_final_graduacion') {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

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
                'estudiante' => $trabajofinaleditar->estudiante,
                'modalidad' => $trabajofinaleditar->modalidad,
                'grado' => $trabajofinaleditar->grado,
                'postgrado' => $trabajofinaleditar->postgrado,
                'carga' => $carga->nombre,
                'vigencia' => $trabajofinaleditar->fecha_inicio.'/'.$trabajofinaleditar->fecha_fin,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function Eliminar_trabajofinal_graduacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $actividadeliminar = Actividad::find($request->id);

            if ($actividadeliminar->categoria !== 'trabajo_final_graduacion') {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            // pendiente el metodo eliminar real
            $actividadeliminar->estado_id = $this->obtenerestado('inactivo');
            $actividadeliminar->save();

            $carga = Carga::find($actividadeliminar->carga_id);

            return response()->json([
                'message' => 'se ha eliminado con exito el trabajo final de graduacion',
                'id' => $actividadeliminar->id,
                'tipo' => $actividadeliminar->tipo,
                'estudiante' => $actividadeliminar->estudiante,
                'modalidad' => $actividadeliminar->modalidad,
                'grado' => $actividadeliminar->grado,
                'postgrado' => $actividadeliminar->postgrado,
                'carga' => $carga->nombre,
                'vigencia' => $actividadeliminar->fecha_inicio.'/'.$actividadeliminar->fecha_fin,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    }

    public function Buscar_trabajofinal_graduacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:actividades,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $actividadbuscada = Actividad::find($request->id);

            if ($actividadbuscada->categoria !== 'trabajo_final_graduacion') {
                return response()->json(['error' => 'metodo no valido'], 422);
            }

            $carga = Carga::find($actividadbuscada->carga_id);

            return response()->json([
                'message' => ' El trabajo final de graduacion consultado es',
                'id' => $actividadbuscada->id,
                'tipo' => $actividadbuscada->tipo,
                'estudiante' => $actividadbuscada->estudiante,
                'modalidad' => $actividadbuscada->modalidad,
                'grado' => $actividadbuscada->grado,
                'postgrado' => $actividadbuscada->postgrado,
                'carga' => $carga->nombre,
                'fecha_inicio' => $actividadbuscada->fecha_inicio,
                'fecha_fin' => $actividadbuscada->fecha_fin,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    }

    // Proyectos de investigacion Accionsocial P6
    public function Agregar_proyecto_accion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero' => 'required',
            'nombre' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'carga_id' => 'required| exists:cargas,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $usuario = $request->user();

            $nuevoproyectoaccion = Actividad::create([
                'categoria' => 'proyecto_investigacion_accion_social',
                'nombre' => $request->nombre,
                'numero_oficio' => $request->numero,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'carga_id' => $request->carga_id,
                'usuario_id' => $usuario->id,
                'estado_id' => $this->obtenerestado('activo'),
            ]);

            $carga = Carga::find($request->carga_id);

            DB::commit();

            return response()->json([
                'message' => 'Se ha ingresado Proyecto de Investigacion/Accion',
                'no' => $nuevoproyectoaccion->numero_oficio,
                'nombre' => $nuevoproyectoaccion->nombre,
                'vigencia' => $nuevoproyectoaccion->fecha_inicio.' / '.$nuevoproyectoaccion->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function obtenerestado($estado)
    {
        switch ($estado) {
            case 'activo':
                $estadoactivo = Estado::where('nombre', 'like', 'activo')->first();

                return $estadoactivo->id;
                break;

            case 'inactivo':
                $estadoinactivo = Estado::where('nombre', 'like', 'inactivo')->first();

                return $estadoinactivo->id;
                break;

            default:
                break;
        }
    }

    public function Listar_proyectos_accion(Request $request)
    {
        $usuario = $request->user();

        try {
            $estado = $this->obtenerestado('activo');
            $proyectosaccion = Actividad::where('usuario_id', $usuario->id)->where('categoria', 'proyecto_investigacion_accion_social')->where('estado_id', $estado)->get();

            if ($proyectosaccion->isEmpty()) {
                return response()->json(['message' => 'no se encuentran proyectos de ivestigacion o accion social'], 200);
            }

            $listaactividades = [];

            foreach ($proyectosaccion as $actividad) {
                $carga = Carga::find($actividad['carga_id']);

                $lineaactividad = (object) [
                    'id' => $actividad['id'],
                    'numero' => $actividad['numero_oficio'],
                    'nombre' => $actividad['nombre'],
                    'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
                    'carga' => $carga->nombre,
                ];
                $listaactividades[] = $lineaactividad;
            }

            return response()->json(['TIAC' => $listaactividades], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function Editar_proyectos_accion(Request $request)
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

            if ($actividadeditar->categoria !== 'proyecto_investigacion_accion_social') {
                return response()->json(['error' => 'metodo no valido'], 422);
            }
            $actividadeditar->numero_oficio = $request->numero;
            $actividadeditar->nombre = $request->nombre;
            $actividadeditar->fecha_inicio = $request->fecha_inicio;
            $actividadeditar->fecha_fin = $request->fecha_fin;
            $actividadeditar->save();
            $carga = Carga::find($actividadeditar->carga_id);

            return response()->json([
                'message' => 'se ha editado con exito el proyecto de accion social',
                'id' => $actividadeditar->id,
                'numero' => $actividadeditar->numero_oficio,
                'nombre' => $actividadeditar->nombre,
                'vigencia' => $actividadeditar->fecha_inicio.'/'.$actividadeditar->fecha_fin,
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
            if ($trabajoeliminar->categoria !== 'proyecto_investigacion_accion_social') {
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
                'vigencia' => $trabajoeliminar->fecha_inicio.'/'.$trabajoeliminar->fecha_fin,
            ], 200);
        } catch (\Exception $e) {
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

            if ($actividadbuscada->categoria !== 'proyecto_investigacion_accion_social') {
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
        } catch (\Exception $e) {
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
                'vigencia' => $nuevaactividad->fecha_inicio.' / '.$nuevaactividad->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);
        } catch (\Exception $e) {
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
                    'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
                    'carga' => $carga->nombre,
                ];
                $listaactividades[] = $lineaactividad;
            }

            return response()->json(['DAC' => $listaactividades], 200);
        } catch (\Exception $e) {
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

            if ($actividadeditar->categoria !== 'cargo_dac') {
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
                'vigencia' => $actividadeditar->fecha_inicio.'/'.$actividadeditar->fecha_fin,
            ], 200);
        } catch (\Exception $e) {
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
            if ($actividadeliminar->categoria !== 'cargo_dac') {
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
                'vigencia' => $actividadeliminar->fecha_inicio.'/'.$actividadeliminar->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);
        } catch (\Exception $e) {
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

            if ($actividadbuscada->categoria !== 'cargo_dac') {
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
        } catch (\Exception $e) {
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
                'vigencia' => $nuevaactividad->fecha_inicio.' / '.$nuevaactividad->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);
        } catch (\Exception $e) {
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
                    'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
                    'carga' => $carga->nombre,
                ];

                $listaactividades[] = $lineaactividad;
            }

            return response()->json(['OTL' => $listaactividades], 200);
        } catch (\Exception $e) {
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

            if ($actividadeditar->categoria !== 'otra_labor') {
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
                'vigencia' => $actividadeditar->fecha_inicio.'/'.$actividadeditar->fecha_fin,
                'carga' => $carga->nombre,
            ], 200);
        } catch (\Exception $e) {
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

            if ($trabajoeliminar->categoria !== 'otra_labor') {
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
                'vigencia' => $trabajoeliminar->fecha_inicio.'/'.$trabajoeliminar->fecha_fin,
            ], 200);
        } catch (\Exception $e) {
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

            if ($actividadbuscada->categoria !== 'otra_labor') {
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
        } catch (\Exception $e) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    }

    // METODO PARA OBTENER TODAS LAS OTRAS ACTIVIDADES DE UN USUARIO PARA P6
    public function Disparador(Request $request)
    {
        $usuario = $request->user();

        try {
            $fechaparametro = Carbon::now()->format('Y-m-d');

            $actividadesTFG = $this->Cargar_actividad_categoria('trabajo_final_graduacion', $usuario->id, $fechaparametro);

            $actividadesPIAC = $this->Cargar_actividad_categoria('proyecto_investigacion_accion_social', $usuario->id, $fechaparametro);

            $actividadesDAC = $this->Cargar_actividad_categoria('cargo_dac', $usuario->id, $fechaparametro);

            $actividadesOTRA = $this->Cargar_actividad_categoria('otra_labor', $usuario->id, $fechaparametro);

            $listaactividades = (object) [
                'TFG' => $actividadesTFG,
                'PIAC' => $actividadesPIAC,
                'DAC' => $actividadesDAC,
                'OTRA' => $actividadesOTRA,
            ];

            return $listaactividades;
        } catch (\Exception $e) {
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
                case 'trabajo_final_graduacion':
                    foreach ($actividadenvigencia as $actividad) {
                        $carga = Carga::find($actividad['carga_id']);

                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'tipo' => $actividad['tipo'],
                            'estudiante' => $actividad['estudiante'],
                            'modalidad' => $actividad['modalidad'],
                            'grado' => $actividad['grado'],
                            'postgrado' => $actividad['postgrado'],
                            'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
                            'carga' => $carga->nombre,
                        ];

                        $listadoactividad[] = $lineaactividad;
                    }

                    return $listadoactividad;

                    break;

                case 'proyecto_investigacion_accion_social':
                    foreach ($actividadenvigencia as $actividad) {
                        $carga = Carga::find($actividad['carga_id']);

                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'numero' => $actividad['numero_oficio'],
                            'nombre' => $actividad['nombre'],
                            'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
                            'carga' => $carga->nombre,
                        ];

                        $listadoactividad[] = $lineaactividad;
                    }

                    return $listadoactividad;

                    break;

                case 'cargo_dac':
                    foreach ($actividadenvigencia as $actividad) {
                        $carga = Carga::find($actividad['carga_id']);
                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'numero_oficio' => $actividad['numero_oficio'],
                            'cargo_comision' => $actividad['cargo_comision'],
                            'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
                            'carga' => $carga->nombre,
                        ];

                        $listadoactividad[] = $lineaactividad;
                    }

                    return $listadoactividad;
                    break;

                case 'otra_labor':
                    foreach ($actividadenvigencia as $actividad) {
                        $carga = Carga::find($actividad['carga_id']);

                        $lineaactividad = (object) [
                            'id' => $actividad['id'],
                            'numero' => $actividad['numero_oficio'],
                            'nombre' => $actividad['nombre'],
                            'vigencia' => $actividad['fecha_inicio'].'/'.$actividad['fecha_fin'],
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
