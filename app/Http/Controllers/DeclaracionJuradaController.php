<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeclaracionJuradaController extends Controller
{
    public function agregue(Request $request)
    {
        $validator =
        Validator::make($request->all(), [
            'observaciones' => 'required',
            'unidad_academica' => 'required',
            'trabajos' => 'required|array|min:1',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $declaracionJurada = DeclaracionJurada::create([
                'observaciones' => $request->observaciones,
                'unidad_academica' => $request->unidad_academica,
                'usuario_id' => $request->user()->id,
            ]);
            foreach ($trabajos as $trabajo) {
                TrabajosDeclaraciones::create([
                    'trabajo_id' => $trabajo->id,
                    'declaracion_jurada_id' => $declaracionJurada->id,
                ]);
            }
            DB::commit();
            return response()->json(['Message' => 'Se ha registrado con éxito'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 422);
        }

    }
}
