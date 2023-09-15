<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Canton;

class CantonController extends Controller
{

    public function obtenga(Request $request)
    {
        if ($request->has('id_provincia')) {
            return Canton::where('id_provincia', $request->get('id_provincia'))->get()->toJson();
        }
        if ($request->has('id')) {
            return Canton::find($request->get('id'))->toJson();
        }
        return Canton::all()->toJson();
    }
}
