<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleSolicitud;
use Illuminate\Support\Facades\Validator;

class DetalleSolicitudController extends Controller
{
    public function muestreElDetalleDeLaSolicitud(Request $request){
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        return DetalleSolicitud::where('id_solicitud',$validatedData['id'])->with('curso')->with('solicitudCurso')->first();
    }
}
