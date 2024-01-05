<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\AprobacionSolicitudCurso;
use App\Models\Carga;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Persona;
use App\Models\SolicitudGrupo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PSeis;
use App\Models\Curso;
use App\Models\PSeisCursosAprobados;
use App\Models\SolicitudCurso;
use Exception;
use Illuminate\Http\Request;
use Validator;

class PSeisController extends Controller
{
    public function crearP6(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'profesor_id' => 'required',
            'solicitud_curso_id' => 'required',
            'cargo_categoria' => 'required',
            'jornada_id' => 'required',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'cursos' => 'nullable|array',
            'DAC' => 'nullable|array',
            'PIAC' => 'nullable|array',
            'TFG' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $nuevap6 = PSeis::create([
                'profesor_id' => $request->profesor_id,
                'solicitud_curso_id' => $request->solicitud_curso_id,
                'jornada_id' => $request->jornada_id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'cargo_categoria' => $request->cargo_categoria
            ]);

            //Recorremos cada arreglo que viene del request para agregar si alguno de los arrays no viene vacío 
            $acciones = [];

            if ($nuevap6) {
                if (!empty($request->cursos)) {
                    $agregarcursos = $this->Agregar_cursos_p6($request->cursos, $nuevap6->id);
                    $acciones[] = $agregarcursos;
                }
                if (!empty($request->DAC)) {
                    $agregardac = $this->Agregar_cargo_DAC($request->DAC, $request->profesor_id, $nuevap6->id);
                    $acciones[] = $agregardac;
                }

                if (!empty($request->PIAC)) {
                    $agregarpiac = $this->Agregar_PIAC($request->PIAC, $request->profesor_id, $nuevap6->id);
                    $acciones[] = $agregarpiac;
                }

                if (!empty($request->TFG)) {

                    $agregartfg = $this->Agregar_TFG($request->TFG, $request->profesor_id, $nuevap6->id);
                    $acciones[] = $agregartfg;
                }

                if (!empty($request->OT)) {

                    $aceptarot = $this->Agregar_OT($request->OT, $request->profesor_id, $nuevap6->id);
                    $acciones[] = $aceptarot;
                }
                DB::commit();
                return response()->json(['message' => 'P6 creada exitosamente', 'acciones' => $acciones]);

            } else {
                DB::rollback();
                return response()->json(['error' => "No se ha podido crear la p6"], 500);
            }

        } catch (Exception $e) {
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
    public function Agregar_cursos_p6($arreglocursos, $pseis_id)
    {
        $acciones = [];
        foreach ($arreglocursos as $curso) {
            try {

                $referencia = $this->Obtener_datos_solicitud($curso['solicitud_grupo_id']);
                $existereferencia = PSeisCursosAprobados::where('p_seis_id', $pseis_id)->where('curso_aprobado_id', $referencia->id)->first();

                if (!$existereferencia) {
                    $nuevalineacursos = PSeisCursosAprobados::create([
                        'p_seis_id' => $pseis_id,
                        'curso_aprobado_id' => $referencia->id,
                    ]);
                    $acciones[] = "Se añadio la referencia a la p6";
                } else {
                    $acciones[] = "Se proceso la referencia a la p6";
                }

            } catch (Exception $e) {
                return ($e->getMessage());
            }

        }
        return $acciones;
    }
    public function Agregar_cargo_DAC($arregloDAC, $profesor_id, $pseis_id)
    {
        $categoria = Categoria::where('nombre', 'cargo_docente')->first();
        try {

            foreach ($arregloDAC as $dac) {

                $carga = Carga::where('nombre', $dac['cargaAsignadaCC'])->first();
                $nuevaactividad = Actividad::create([
                    'p_seis_id' => $pseis_id,
                    'categoria_id' => $categoria->id,
                    'numero_oficio' => $dac['numeroOficio'],
                    'cargo_comision' => $dac['cargoComision'],
                    'fecha_inicio' => $dac['vigenciaDesdeDAC'],
                    'fecha_fin' => $dac['vigenciaHastaDAC'],
                    'carga_id' => $carga->id,
                    'estado_id' => $this->obtenerestado('activo'),
                ]);
            }

            return ['se han agregado cargos DAC'];

        } catch (Exception $e) {

            return ['error' . $e->getMessage()];
        }

    }
    public function Agregar_PIAC($arregloPIAC, $profesor_id, $pseis_id)
    {
        $categoria = Categoria::where('nombre', 'proyectos')->first();

        try {

            foreach ($arregloPIAC as $piac) {

                $carga = Carga::where('nombre', $piac['cargaAsignadaProyecto'])->first();

                $nuevaactividad = Actividad::create([
                    'p_seis_id' => $pseis_id,
                    'categoria_id' => $categoria->id,
                    'nombre' => $piac['nombreProyecto'],
                    'numero_oficio' => $piac['numeroProyecto'],
                    'fecha_inicio' => $piac['vigenciaDesdePIAC'],
                    'fecha_fin' => $piac['vigenciaHastaPIAC'],
                    'carga_id' => $carga->id,
                    'estado_id' => $this->obtenerestado('activo'),
                ]);
            }
            return ['se han agregado PIAC'];

        } catch (Exception $e) {

            return ['error' . $e->getMessage()];
        }
    }
    public function Agregar_TFG($arregloTFG, $profesor_id, $pseis_id)
    {
        $categoria = Categoria::where('nombre', 'trabajos_finales')->first();
        try {

            foreach ($arregloTFG as $tfg) {

                $carga = Carga::where('nombre', $tfg['cargaAcademicaEstudiante'])->first();

                $nuevaactividad = Actividad::create([
                    'p_seis_id' => $pseis_id,
                    'categoria_id' => $categoria->id,
                    'tipo' => $tfg['tipoTFG'],
                    'estudiante' => $tfg['carnetEstudiante'] . ' ' . $tfg['nombreEstudiante'],
                    'modalidad' => $tfg['modalidadTFG'],
                    'grado' => $tfg['gradoEstudiante'],
                    'postgrado' => $tfg['posgradoEstudiante'],
                    'fecha_inicio' => $tfg['vigenciaDesdeTFG'],
                    'fecha_fin' => $tfg['vigenciaHastaTFG'],
                    'carga_id' => $carga->id,
                    'estado_id' => $this->obtenerestado('activo'),
                ]);
            }
            return ['se han agregado TFG'];

        } catch (Exception $e) {

            return ['error' . $e->getMessage()];
        }
    }
    public function Agregar_OT($arregloOT, $profesor_id, $pseis_id)
    {
        $categoria = Categoria::where('nombre', 'otro')->first();
        try {

            foreach ($arregloOT as $ot) {
                $carga = Carga::where('nombre', $ot['cargaAsignadaOT'])->first();

                $nuevaactividad = Actividad::create([
                    'p_seis_id' => $pseis_id,
                    'categoria_id' => $categoria->id,
                    'cargo_comision' => $ot['cargo'],
                    'nombre' => $ot['nombre'],
                    'fecha_inicio' => $ot['vigenciaDesdeOT'],
                    'fecha_fin' => $ot['vigenciaHastaOT'],
                    'carga_id' => $carga->id,
                    'estado_id' => $this->obtenerestado('activo'),
                ]);
            }
            return ['se han agregado OT'];

        } catch (Exception $e) {

            return ['error' . $e->getMessage()];
        }
    }
    public function Obtener_datos_solicitud($solicitudGrupoID)
    {
        $informacionSolicitud = SolicitudCurso::join('detalle_solicitudes', 'solicitud_cursos.id', '=', 'detalle_solicitudes.solicitud_curso_id')
            ->join('solicitud_grupos', 'detalle_solicitudes.id', '=', 'solicitud_grupos.detalle_solicitud_id')
            ->where('solicitud_grupos.id', $solicitudGrupoID)
            ->select(
                'solicitud_cursos.id as solicitud_id'
            )
            ->first();
        $aprobacion = AprobacionSolicitudCurso::where('solicitud_curso_id', $informacionSolicitud->solicitud_id)->first();
        return $aprobacion;
    }

    public function listarP6_Usuario(Request $request)
    {
        $usuario = $request->user();
        $listado_p6 = PSeis::where('profesor_id', $usuario->id)->get();
        $listacompletada = [];
        foreach ($listado_p6 as $p6) {
            $jornada = Carga::find($p6->jornada_id);
            $formatocreatedat = Carbon::parse($p6->created_at)->format('Y-m-d');
            $lineap6 = (object) [
                'id' => $p6->id,
                'Cargo_categoria' => $p6->cargo_categoria,
                'jornada' => $jornada->nombre,
                'fecha_creacion' => $formatocreatedat,
                'fecha_inicio' => $p6->fecha_inicio,
                'fecha_fin' => $p6->fecha_fin,
            ];
           
            $listacompletada [] = $lineap6;
        }

        return($listacompletada);
    }
    public function Obtener_datos_p6Usuario(Request $request)
    {
        $usuario = $request->user();
        $pseis = PSeis::where('profesor_id', $usuario->id)->get();

        foreach ($pseis as $p6) {
            $cursosasoacidos = $this->Cargarcursos_p6id($p6['id'], $p6['profesor_id']);
            $actividadesasociadas = $this->Cargar_Actividades($p6['id']);
            return response()->json([
                'P6 info' => $p6,
                'actividades' => $actividadesasociadas,
                'cursos' => $cursosasoacidos
            ]);
        }
    }

    public function Cargarcursos_p6id($pseis_id, $profesor_id)
    {
        $cursos_aprobados = PSeisCursosAprobados::where('p_seis_id', $pseis_id)->first();
        $aprobacion = AprobacionSolicitudCurso::find($cursos_aprobados->curso_aprobado_id);
        $gruposJoin = DB::table('solicitud_grupos')->where('profesor_id', $profesor_id)
            ->leftJoin('detalle_solicitudes', 'solicitud_grupos.detalle_solicitud_id', '=', 'detalle_solicitudes.id')
            ->leftJoin('solicitud_cursos', 'detalle_solicitudes.solicitud_curso_id', '=', 'solicitud_cursos.id')
            ->where('solicitud_cursos.id', $aprobacion->solicitud_curso_id)
            ->select(
                'detalle_solicitudes.curso_id as curso_id',
                'solicitud_grupos.id as grupo_id',
                'solicitud_grupos.carga_id as carga_id',
                'solicitud_grupos.grupo as grupo',
                'solicitud_grupos.horas as horas',
                'solicitud_grupos.recinto as recinto',
                'solicitud_grupos.cupo as cupo',
                'detalle_solicitudes.id as detalle_id',
                'solicitud_cursos.id as solicitud_curso_id'
            )
            ->get();
        if ($gruposJoin) {
            $grupos_profesor = [];
            foreach ($gruposJoin as $lineacurso) {
                $carga = Carga::find($lineacurso->carga_id);
                $curso = Curso::find($lineacurso->curso_id);
                $infocurso = (object) [
                    'curso_id' => $curso->id,
                    'sigla' => $curso->sigla,
                    'nombre' => $curso->nombre,
                    'grado_anual' => $curso->grado_anual,
                    'grupo' => $lineacurso->grupo,
                    'cupo' => $lineacurso->cupo,
                    'recinto' => $lineacurso->recinto,
                    'horas' => $lineacurso->horas,
                    'carga' => $carga->nombre,
                    'id_solicitud_grupo' => $lineacurso->grupo_id,
                    'detalle_id' => $lineacurso->detalle_id,
                    'solicitud_curso' => $lineacurso->solicitud_curso_id
                ];
                $grupos_profesor[] = $infocurso;
            }
            return $grupos_profesor;
        }
    }

    public function Cargar_Actividades($pseis_id)
    {
        $actividades = Actividad::where('p_seis_id', $pseis_id)->get();
        return $actividades;
    }

}
