<?php

namespace App\Http\Controllers;

use App\Models\Canton;
use Illuminate\Http\Request;

class CantonController extends Controller
{
    public function obtenga(Request $request)
    {
        if ($request->has('provincia_id')) {
            return Canton::where('provincia_id', $request->get('provincia_id'))->get()->toJson();
        }
        if ($request->has('id')) {
            return Canton::find($request->get('id'))->toJson();
        }

        return Canton::all()->toJson();
    }
}
