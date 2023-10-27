<?php

namespace App\Http\Controllers;

use App\Models\SolicitudGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudGrupoController extends Controller
{
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
