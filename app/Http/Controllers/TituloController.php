<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Titulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TituloController extends Controller
{
    public function obtenga(Request $request)
    {
        return response()->json($request->user()->persona->titulo);
    }

    public function guarde(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'archivos' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $persona = null;
        if ($request->persona) {
            $persona = $request->persona;
        } else {
            $persona = $request->user()->persona;
        }
        try {
            $nombreArchivo = str_replace(' ', '_', $persona->nombre) . '_documentoAdicional'; // Nombre del archivo
            Titulo::create([
                'nombre' => $nombreArchivo,
                'archivo' => $request->archivos,
                'persona_id' => $persona->id,
            ]);
            return response()->json(['message' => 'Guardado exitosamente'], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public function elimine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        try {
            $archivo = Titulo::find($request->id);
            $archivo->delete();
            return response()->json(['message' => 'Eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
