<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estado;

class EstadosController extends Controller
{
    public function Estado_por_nombre($nombre)
    {
        switch ($nombre) {

            case 'activo':
                $estado = Estado::where('nombre', 'like', $nombre)->first();
                return $estado->id;

                break;

            case 'inactivo':
                $estado  = Estado::where('nombre', 'like', $nombre)->first();
                return $estado ->id;
                break;

            case 'pendiente':
                $estado = Estado::where('nombre', 'like', $nombre)->first();
                return $estado->id;
                break;

            case 'aprobado':
                $estado = Estado::where('nombre', 'like', $nombre)->first();
                return $estado->id;
                break;

            case 'rechazado':
                $estado = Estado::where('nombre', 'like', $nombre)->first();
                return $estado->id;

                break;

            case 'incompleta':
                $estado = Estado::where('nombre', 'like', $nombre)->first();
                return $estado->id;
                break;

            default:

                break;
        }
    }
}
