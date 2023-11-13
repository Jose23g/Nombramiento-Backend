<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use Illuminate\Http\Request;

class DistritoController extends Controller
{
    public function obtenga(Request $request)
    {
        if ($request->has('canton_id')) {
            return Distrito::where('canton_id', $request->get('canton_id'))->get()->toJson();
        }
        if ($request->has('id')) {
            return Distrito::find($request->get('id'))->toJson();
        }

        return Distrito::all()->toJson();
    }
}
