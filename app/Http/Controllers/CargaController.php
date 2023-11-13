<?php

namespace App\Http\Controllers;

use App\Models\Carga;

class CargaController extends Controller
{
    public function obtengaLaListaDeCargas()
    {
        return Carga::all()->toJson();
    }
}
