<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barrio;
class BarrioController extends Controller
{

    public function obtenga(Request $request){
        if ($request->has('id_provincia') && $request->has('id_canton') && $request->has('id_distrito')) {
            return Barrio::where('id_provincia', $request->get('id_provincia'))->where('id_canton', $request->get('id_canton'))->where('id_distrito', $request->get('id_distrito'))->get()->toJson();
        }
        if($request->has('id_provincia')) {
            return Barrio::where('id_provincia', $request->get('id_provincia'))->get()->toJson();
        }
        if($request->has('id_canton')) {
            return Barrio::where('id_canton', $request->get('id_canton'))->get()->toJson();
        }
        if ($request->has('id_distrito')) {
            return Barrio::where('id_distrito', $request->get('id_distrito'))->get()->toJson();
        }
        if ($request->has('id')) {
            return Barrio::find($request->get('id'))->toJson();
        }
        return Barrio::all()->toJson();
    }
}
