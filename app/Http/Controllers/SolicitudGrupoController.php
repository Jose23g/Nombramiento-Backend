<?php

namespace App\Http\Controllers;

use App\Models\SolicitudGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudGrupoController extends Controller
{
    public function agregue(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'profesor_id' => 'required',
                'detalle_solicitud_id' => 'required',
                'carga_id' => 'required',
                'grupo' => 'required',
                'cupo' => 'required',
                'recinto' => 'required',
            ], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $solicitudGrupo = SolicitudGrupo::create([
            'profesor_id' => $request->profesor_id,
            'detalle_solicitud_id' => $request->detalle_solicitud_id,
            'carga_id' => $request->carga_id,
            'grupo' => $request->grupo,
            'cupo' => $request->cupo,
            'recinto' => $request->recinto,
        ]);
        $this->modifiqueLasTablas($solicitudGrupo, -1);

        return response()->json(['Message' => 'Se ha registrado con éxito', 'solicitud_grupo_id' => $solicitudGrupo->id], 200);
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
        $solicitudesGrupo = SolicitudGrupo::where('detalle_solicitud_id', $request->id)->with(['carga', 'profesor.persona'])->orderByDesc('id')->get();

        return response()->json($solicitudesGrupo, 200);
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
        $solicitudGrupo = SolicitudGrupo::find($request->id);
        $this->modifiqueLasTablas($solicitudGrupo, 1);
        $solicitudGrupo->delete();

        return response()->json(['Message' => 'Solicitud eliminada'], 200);
    }

    public function modifiqueLasTablas($solicitudGrupo, $inversor)
    {
        $solicitudCurso = $solicitudGrupo->detalleSolicitud->solicitudCurso;
        $detalleSolicitud = $solicitudGrupo->detalleSolicitud;
        $detalleSolicitud->update(['grupos' => max($detalleSolicitud->grupos - ($inversor * 1), 0)]);
        $solicitudCurso->update(['estado_id' => 7, 'carga_total' => max($solicitudCurso->carga_total - ($inversor * $this->obtengaElValor($solicitudGrupo->carga->nombre)), 0)]);
    }

    public function obtengaElValor($nombre)
    {
        $numero = 0;
        if (strpos($nombre, ' - ') !== false) {
            list($fraccion, $numero) = explode(' - ', $nombre);
        } else {
            $numero = $nombre;
        }

        return floatval($numero);
    }
}
