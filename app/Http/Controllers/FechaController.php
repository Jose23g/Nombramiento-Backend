<?php

namespace App\Http\Controllers;

use App\Models\Fecha;
use Illuminate\Support\Carbon;

class FechaController extends Controller
{
    public function obtengaLaListaDeFechas()
    {
        $fechas = Fecha::all()->filter(function ($fecha) {
            $fechaFinalOriginal = Carbon::parse($fecha->fecha_fin);
            $fechaFinalExtendida = $fechaFinalOriginal->addMonth(1);
            $fechaInicio = Carbon::parse($fecha->fecha_inicio);
            $fechaActual = Carbon::now();

            return $fechaActual->between($fechaInicio, $fechaFinalExtendida) && $fecha->tipo_id == 1;
        })->values()->toArray();

        return $fechas;
    }
    public function agregueParaUnTrabajoInterno($request)
    {
        $fecha = Fecha::create([
            'tipo_id' => 4,
            'fecha_inicio' => $request['fecha_inicio'],
            'fecha_fin' => $request['fecha_fin'],
        ]);
        return $fecha;
    }
    public function modifique($request)
    {
        Fecha::find($request['id'])->update([
            'fecha_inicio' => $request['fecha_inicio'],
            'fecha_fin' => $request['fecha_fin'],
        ]);
    }
}
