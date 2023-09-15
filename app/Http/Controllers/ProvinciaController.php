<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provincia;

class ProvinciaController extends Controller
{
    public function obtengaLaLista()
    {
        return Provincia::all()->toJson();
    }
    public function obtengaPorId(Request $request)
    {
        if ($request->has('id')) {
            return Provincia::find($request->get('id'))->toJson();
        }
        return response()->json('error: No se han enviado los datos solicitados', 441);
    }
}
