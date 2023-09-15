<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Canton;

class CantonController extends Controller
{
    public function obtengaPorId(Request $request)
    {
        if ($request->has('id')) {
            return Canton::find($request->get('id'))->toJson();
        }
        return response()->json('error: No se han enviado los datos solicitados', 441);
    }
    public function obtengaLaListaPorProvincia(Request $request)
    {
        if ($request->has('id_provincia')) {
            return Canton::where('id_provincia', $request->get('id_provincia'))->get()->toJson();
        }
        return response()->json('error: No se han enviado los datos solicitados', 441);
    }
}
