<?php

namespace App\Http\Controllers;

use App\Models\DetalleSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetalleSolicitudController extends Controller
{
    public function agregue(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'solicitud_curso_id' => 'required',
                'curso_id' => 'required',
            ], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $detalleSolicitud = DetalleSolicitud::create([
                'solicitud_curso_id' => $request->solicitud_curso_id,
                'curso_id' => $request->curso_id,
                'grupos' => 0,
        ]);
        $detalleSolicitud->solicitudCurso->update(['estado_id' => 7]);

        return response()->json(['Message' => 'Se ha registrado con éxito', 'detalle_solicitud_id' => $detalleSolicitud->id], 200);
    }

    public function obtengaLaLista(Request $request)
    {
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $detalleSolicitudes = DetalleSolicitud::where('solicitud_curso_id', $request->id)->with(['curso.planEstudios'])->orderByDesc('id')->get();

        return response()->json($detalleSolicitudes, 200);
    }

    public function elimineElDetalle(Request $request)
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
        $detalleSolicitud = DetalleSolicitud::find($request->id);
        $numero = 0;
        $detalleSolicitud->solicitudGrupos->map(function ($solicitud) use (&$numero) {
            $numero += app(SolicitudGrupoController::class)->obtengaElValor($solicitud->carga->nombre);
        });
        $solicitudCurso = $detalleSolicitud->solicitudCurso;
        $solicitudCurso->update(['estado_id' => 7, 'carga_total' => $solicitudCurso->carga_total - $numero]);
        $detalleSolicitud->delete();

        return response()->json(['Message' => 'Detalle eliminado'], 200);
    }
}
