<?php

namespace App\Http\Controllers;

use App\Models\DeclaracionJurada;
use App\Models\TrabajoDeclaracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'observacion' => $request->observaciones,
                'unidad_academica' => $request->unidad_academica,
                'usuario_id' => $request->user()->id,
            ]);
            foreach ($request->trabajos as $trabajo) {
                TrabajoDeclaracion::create([
                    'trabajo_id' => $trabajo['id'],
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
    public function obtengaLaUltimaDeclaracion(Request $request)
    {
        $declaracion = $request->user()->declaraciones()->orderBy('id', 'DESC')->first();
        $declaracionJurada = $request->user()->declaraciones()->with(['trabajos.fecha', 'trabajos.horarioTrabajos'])->orderBy('id', 'DESC')->first();
        $trabajosInternos = $declaracionJurada->trabajos->filter(function ($trabajo) {
            return $trabajo->tipo_id == 2;
        });
        $trabajosExternos = $declaracionJurada->trabajos->filter(function ($trabajo) {
            return $trabajo->tipo_id == 3;
        });
        $profesor = app(UsuarioController::class)->obtengaElProfesorActual($request);
        return response()->json(['profesor' => $profesor, 'declaracion' => $declaracion, 'trabajos_internos' => $trabajosInternos, 'trabajos_externos' => $trabajosExternos]);
    }
}
