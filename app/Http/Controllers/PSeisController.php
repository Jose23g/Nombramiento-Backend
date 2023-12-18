<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\AprobacionSolicitudCurso;
use App\Models\Carga;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\PSeis;
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
            'cargo_categoria' => 'required',
            'jornada_id' => 'required',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'cursos' => 'required|array',
            'DAC' => 'required|array',
            'PIAC' => 'required|array',
            'TFG' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $nuevap6 = PSeis::create([
                'profesor_id' => $request->profesor_id,
                'jornada_id' => $request->jornada_id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'cargo_categoria' => $request->cargo_categoria
            ]);
            //Recorremos cada arreglo que viene del request para agregar si alguno de los arrays no viene vacío 
            if ($nuevap6) {

                if (!$request->cursos->isEmpty()) {

                }

                if (!$request->DAC->isEmpty()) {
                    $agregardac = $this->Agregar_cargo_DAC($request->DAC, $request->profesor_id, $nuevap6->id);
                }

                if (!$request->PIAC->isEmpty()) {
                    $agregarpiac = $this->Agregar_PIAC($request->PIAC, $request->profesor_id, $nuevap6->id);
                }

                if (!$request->TFG->isEmpty()) {

                }

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
        foreach ($arreglocursos as $curso) {

            $referencia = $this->Obtener_datos_solicitud($curso['solicitud_grupo_id']);

            $nuevalineacursos = PSeisCursosAprobados::create([
                'p_seis_id' => $pseis_id,
                'curso_aprobado_id' => $referencia['id'] ?? null,
            ]);

        }
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
                    'numero_oficio' => $dac['nOficio'],
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
                    'usuario_id' => $profesor_id,
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
                    'categoria' => $categoria->id,
                    'tipo' => $tfg['tipoTFG'],
                    'estudiante' => $tfg['carnetEstudiante'] . '' . $tfg['nombreEstudiante'],
                    'modalidad' => $tfg['modalidadTFG'],
                    'grado' => $tfg['gradoEstudiante'],
                    'postgrado' => $tfg['posgradoEstudiante'],
                    'fecha_inicio' => $tfg['vigenciaDesdeTFG'],
                    'fecha_fin' => $tfg['vigenciaHastaTFG'],
                    'carga_id' => $carga->id,
                    'usuario_id' => $profesor_id,
                    'estado_id' => $this->obtenerestado('activo'),
                ]);
            }
            return ['se han agregado TFG'];

        } catch (Exception $e) {

            return ['error' . $e->getMessage()];
        }
    }

    public function Obtener_datos_solicitud(Request $request)
    {
        $informacionSolicitud = SolicitudCurso::join('detalle_solicitudes', 'solicitud_cursos.id', '=', 'detalle_solicitudes.solicitud_curso_id')
            ->join('solicitud_grupos', 'detalle_solicitudes.id', '=', 'solicitud_grupos.detalle_solicitud_id')
            ->where('solicitud_grupos.id', $request->solicitudGrupoID)
            ->select(
                'solicitud_cursos.id as solicitud_id'
            )
            ->first();

        $aprobacion = AprobacionSolicitudCurso::where('solicitud_curso_id', $informacionSolicitud->solicitud_id)->first();

        $profesorid = 5;

        $prueba = SolicitudCurso::join('detalle_solicitudes', 'solicitud_cursos.id', '=', 'detalle_solicitudes.solicitud_curso_id')
            ->join('solicitud_grupos', 'detalle_solicitudes.id', '=', 'solicitud_grupos.detalle_solicitud_id')
            ->where('solicitud_cursos.id', $request->solicitud_id)
            ->where('solicitud_grupos.profesor_id', $profesorid)
            ->select(
                'detalle_solicitudes.curso_id as curso_id',
                'solicitud_grupos.id as solicitud_grupos_id',
                'solicitud_grupos.carga_id as solicitud_grupos_carga_id',
                'solicitud_grupos.grupo as solicitud_grupos_grupo',
                'solicitud_grupos.individual_colegiado as solicitud_grupos_individual_colegiado',
                'solicitud_grupos.tutoria as solicitud_grupos_tutoria'
            )
            ->get();

        return response($prueba);
    }

}
