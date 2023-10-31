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
        $detalleSolicitud = $solicitudGrupo->detalleSolicitud;
        $detalleSolicitud->update(['grupos' => $detalleSolicitud->grupos + 1]);
        $numero = 0;
        if (strpos($solicitudGrupo->carga->nombre, ' - ') !== false) {
            list($fraccion, $numero) = explode(' - ', $solicitudGrupo->carga->nombre);
        } else {
            $numero = $solicitudGrupo->carga->nombre;
        }
        $detalleSolicitud->solicitudCurso->update(['carga_total' => $detalleSolicitud->solicitudCurso->carga_total + floatval($numero)]);

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
        $solicitudesGrupo = SolicitudGrupo::where('detalle_solicitud_id', $request->id)->with(['carga', 'profesor.persona'])->get();

        return response()->json($solicitudesGrupo, 200);
    }
}
