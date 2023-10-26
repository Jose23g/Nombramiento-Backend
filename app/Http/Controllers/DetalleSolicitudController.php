<?php

namespace App\Http\Controllers;

use App\Models\DetalleSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetalleSolicitudController extends Controller
{
    public function muestreElDetalleDeLaSolicitud(Request $request)
    {
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        return DetalleSolicitud::where('solicitud_curso_id', $validatedData['id'])->with(['curso', 'solicitudCurso', 'solicitudGrupos'])->first();
    }
}
