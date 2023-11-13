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
}
