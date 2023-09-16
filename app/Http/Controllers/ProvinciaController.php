<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provincia;

class ProvinciaController extends Controller
{

    public function obtenga(Request $request)
    {
        if ($request->has('id')) {
            return Provincia::find($request->get('id'))->toJson();
        }
        return Provincia::all()->toJson();
    }
}
