<?php

namespace App\Http\Controllers;

use App\Models\HorariosGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HorariosGrupoController extends Controller
{
    public function agregue(Request $request)
    {
        $validator =
            Validator::make($request->all(), [
                'dia_id' => 'required',
                'solicitud_grupo_id' => 'required',
                'hora_inicio' => 'required',
                'hora_fin' => 'required',
            ], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        HorariosGrupo::create([
            'dia_id' => $request->dia_id,
            'solicitud_grupo_id' => $request->solicitud_grupo_id,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
        ]);

        return response()->json(['Message' => 'Se ha registrado con éxito'], 200);
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
        $horariosGrupo = HorariosGrupo::where('solicitud_grupo_id', $request->id)->with(['dia'])->get();

        return response()->json($horariosGrupo, 200);
    }
}
