<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Distrito;

class DistritoController extends Controller
{

    public function obtenga(Request $request)
    {
        if ($request->has('id_provincia') && $request->has('id_canton')) {
            return Distrito::where('id_provincia', $request->get('id_provincia'))->where('id_canton', $request->get('id_canton'))->get()->toJson();
        }
        if ($request->has('id_provincia')) {
            return Distrito::where('id_provincia', $request->get('id_provincia'))->get()->toJson();
        }
        if ($request->has('id_canton')) {
            return Distrito::where('id_canton', $request->get('id_canton'))->get()->toJson();
        }
        if ($request->has('id')) {
            return Distrito::find($request->get('id'))->toJson();
        }
        return Distrito::all()->toJson();
    }
}
