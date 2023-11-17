<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BancoController extends Controller
{
    public function obtengaLaLista()
    {
        return Banco::all()->toJson();
    }
    public function obtengaPorId(Request $request)
    {
        $validator =
        Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $banco = Banco::find($request->id);
        if ($banco) {
            return response()->json(['nombre' => $banco->nombre], 200);
        }
        return response()->json(['message' => 'Banco no encontrado'], 400);
    }
}
