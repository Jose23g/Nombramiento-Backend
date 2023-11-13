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
}
