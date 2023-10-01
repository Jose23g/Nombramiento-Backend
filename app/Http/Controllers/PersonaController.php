<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;

class PersonaController extends Controller
{
    public function editePersona(Request $request)
    {
        $persona = Persona::find($request->id_persona);
        $persona->cuenta = $request->cuenta;
        $persona->nombre = $request->nombre;
        $persona->otrassenas = $request->otrassenas;
        $persona->id_banco = $request->id_banco;
        $persona->id_distrito = $request->id_distrito;
        $persona->id_provincia = $request->id_provincia;
        $persona->id_canton = $request->id_canton;
        $persona->save();
    }
}
