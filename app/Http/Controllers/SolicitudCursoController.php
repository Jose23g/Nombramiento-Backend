<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SolicitudCurso;

class SolicitudCursoController extends Controller
{
    public function muestreUnaSolicitud(Request $request){
        $validator =
            Validator::make($request->all(), ['id' => 'required'], [
                'required' => 'El campo :attribute es requerido.',
            ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        return SolicitudCurso::find($validatedData['id'])->with('coordinador')->with('estado')->with('carrera')->first();
    }
}
