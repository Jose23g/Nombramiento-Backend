<?php

namespace App\Http\Controllers;

use App\Models\FechaSolicitud;
use Illuminate\Support\Carbon;

class FechaSolicitudController extends Controller
{
    public function obtengaLaListaDeFechas()
    {
        $fechas = FechaSolicitud::all()->filter(function ($fecha) {
            $fechaFinalOriginal = Carbon::parse($fecha->fecha_fin);
            $fechaFinalExtendida = $fechaFinalOriginal->addMonth(1);
            $fechaInicio = Carbon::parse($fecha->fecha_inicio);
            $fechaActual = Carbon::now();

            return $fechaActual->between($fechaInicio, $fechaFinalExtendida);
        })->values()->toArray();

        return $fechas;
    }
}
