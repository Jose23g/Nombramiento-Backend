<?php

namespace App\Http\Controllers;

use App\Models\AprobacionSolicitudCurso;
use App\Models\DetalleAprobacionCurso;
use App\Models\GrupoAprobado;
use App\Models\SolicitudCurso;
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

    public function cambieElEstadoDeUnaSolicitud(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'id' => 'required',
                'estado_id' => 'required',
                'observacion' => 'required_if:estado_id,=,3',
            ], [
                'required' => 'El campo :attribute es requerido.',
                'required_if' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $solicitudCurso = SolicitudCurso::with('detalleSolicitudes.solicitudGrupos')->find($request->id);

        if ($request->estado_id == 1) {
            $solicitudCurso->update(['estado_id' => $request->estado_id]);
            $cursoAprobado = AprobacionSolicitudCurso::create([
                'solicitud_curso_id' => $request->id,
                'encargado_id' => 8,
            ]);
            $detallesAprobados = $solicitudCurso->detalleSolicitudes->map(function ($detalleSolicitud) use (&$cursoAprobado) {
                $detalleAprobado = DetalleAprobacionCurso::create([
                    'curso_aprobado_id' => $cursoAprobado->id,
                    'detalle_solicitud_id' => $detalleSolicitud->id,
                ]);
                $gruposAprobados = $detalleSolicitud->solicitudGrupos->map(function ($solicitudGrupo) use (&$detalleAprobado) {
                    return GrupoAprobado::create([
                        'detalle_aprobado_id' => $detalleAprobado->id,
                        'solicitud_grupo_id' => $solicitudGrupo->id,
                    ]);
                });
                $detalleAprobado->grupos_aprobados = $gruposAprobados;

                return $detalleAprobado;
            });
            $cursoAprobado->detalles_aprobados = $detallesAprobados;

            return response()->json(['message' => 'Se ha aceptado la solicitud', 'curso_aprobado' => $cursoAprobado], 200);
        } else {
            $solicitudCurso->update(['estado_id' => $request->estado_id, 'observacion' => $request->observacion]);

            return response()->json(['message' => 'Se ha rechazado la solicitud'], 200);
        }
    }
}
