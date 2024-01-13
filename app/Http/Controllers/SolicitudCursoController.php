<?php

namespace App\Http\Controllers;

use App\Models\DetalleSolicitud;
use App\Models\HorariosGrupo;
use App\Models\SolicitudCurso;
use App\Models\SolicitudGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudCursoController extends Controller
{
    public function agregue(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'coordinador_id' => 'required',
                'carrera_id' => 'required',
                'fecha_id' => 'required',
            ], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $solicitudCurso = SolicitudCurso::create([
            'coordinador_id' => $request->coordinador_id,
            'carrera_id' => $request->carrera_id,
            'estado_id' => 7,
            'fecha_id' => $request->fecha_id,
            'observacion' => $request->observacion,
            'carga_total' => 0,
        ]);

        return response()->json(['Message' => 'Se ha registrado con éxito', 'solicitud_curso_id' => $solicitudCurso->id], 200);
    }

    public function obtengaLaLista(Request $request)
    {
        $usuario = $request->user();
        $solicitudesCurso = $usuario->solicitudCursos()->with(['coordinador.persona', 'carrera', 'estado', 'fecha'])->orderByDesc('id')->get();

        return response()->json($solicitudesCurso, 200);
    }

    public function marqueComoPendiente(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'id' => 'required',
            ], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        SolicitudCurso::find($request->id)->update(['estado_id' => 2]);

        return response()->json(['Message' => 'Solicitud completada'], 200);
    }

    public function elimineLaSolicitud(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'id' => 'required',
            ], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        SolicitudCurso::find($request->id)->delete();

        return response()->json(['Message' => 'Solicitud eliminada'], 200);
    }

    public function obtener_informacion_solicitud(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'solicitud_id' => 'required',
        ], [
            'solicitud_id.required' => 'Es necesario proporcionar el solicitud_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $solicitud = SolicitudCurso::where('solicitud_cursos.id', $request->solicitud_id)
                ->join('carreras as carrera', 'solicitud_cursos.carrera_id', '=', 'carrera.id')
                ->join('fechas as fecha', 'solicitud_cursos.fecha_id', '=', 'fecha.id')
                ->join('estados as estado', 'solicitud_cursos.estado_id', '=', 'estado.id')
                ->join('usuarios as usuario', 'solicitud_cursos.coordinador_id', '=', 'usuario.id')
                ->join('personas as persona', 'usuario.persona_id', '=', 'persona.id')
                ->select(
                    'solicitud_cursos.id as solicitud_curso_id',
                    'persona.nombre as coordinador',
                    'carrera.nombre as carrera',
                    'fecha.fecha_inicio',
                    'estado.nombre as estado',
                    'observacion',
                    'carga_total'
                )
                ->first();

            $detalleSolicitud = $this->getDetalleSolicitud($solicitud->solicitud_curso_id);

            return response()->json(['solicitud' => $solicitud, 'Cursos' => $detalleSolicitud]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function getDetalleSolicitud($solicitud_id)
    {
        try {
            $detalle = DetalleSolicitud::where(['solicitud_curso_id' => $solicitud_id])
                ->join('cursos as curso', 'detalle_solicitudes.curso_id', '=', 'curso.id')
                ->select(
                    'detalle_solicitudes.id as detalle_solicitud_id',
                    'curso.sigla',
                    'curso.nombre',
                    'curso.creditos',
                    'curso.grado_anual',
                    'curso.ciclo',
                    'curso.horas_teoricas',
                    'curso.horas_practicas',
                    'curso.horas_laboratorio',
                    'curso.horas',
                    'curso.individual_colegiado',
                    'curso.tutoria',
                    'grupos as total_grupo'
                )
                ->get();

            foreach ($detalle as $curso) {
                $grupos = $this->get_grupos_solicitud($curso->detalle_solicitud_id);
                $curso->grupo = $grupos;
            }

            return $detalle;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_grupos_solicitud($detalle_solicitud_id)
    {
        try {

            $grupos = SolicitudGrupo::where('detalle_solicitud_id', $detalle_solicitud_id)
                ->join('usuarios as usuario', 'solicitud_grupos.profesor_id', '=', 'usuario.id')
                ->join('personas as persona', 'usuario.persona_id', '=', 'persona.id')
                ->join('cargas as carga', 'solicitud_grupos.carga_id', '=', 'carga.id')
                ->select(
                    'solicitud_grupos.id as solicitud_grupo_id',
                    'solicitud_grupos.grupo',
                    'persona.nombre as profesor',
                    'carga.nombre as carga',
                    'solicitud_grupos.cupo',
                    'solicitud_grupos.tutoria',
                    'solicitud_grupos.horas',
                    'solicitud_grupos.recinto',
                )
                ->get();

            foreach ($grupos as $grupo) {
                $horario = $this->get_horario_grupo($grupo->solicitud_grupo_id);
                $grupo->horario = $horario;
            }
            return $grupos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_horario_grupo($solicitud_grupo_id)
    {
        try {
            $horario = HorariosGrupo::where('solicitud_grupo_id', $solicitud_grupo_id)
                ->join('dias as dia', 'horarios_grupos.dia_id', '=', 'dia.id')
                ->select(
                    'dia.nombre as dia',
                    'horarios_grupos.hora_inicio as inicio',
                    'horarios_grupos.hora_fin as fin',
                )
                ->get();
                return $horario;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
