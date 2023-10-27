<?php

namespace App\Http\Controllers;

use App\Models\HorariosGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HorariosGrupoController extends Controller
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
        $horariosGrupo = HorariosGrupo::where('solicitud_grupo_id', $request->id)->with(['dia'])->get();

        return response()->json($horariosGrupo, 200);
    }
}
