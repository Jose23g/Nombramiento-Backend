<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function editePersona(Request $request)
    {
        $persona = Persona::find($request->persona_id);
        $persona->cuenta = $request->cuenta;
        $persona->nombre = $request->nombre;
        $persona->otras_senas = $request->otras_senas;
        $persona->banco_id = $request->banco_id;
        $persona->distrito_id = $request->distrito_id;
        $persona->provincia_id = $request->provincia_id;
        $persona->canton_id = $request->canton_id;
        $persona->save();
        app(TelefonoController::class)->editeTelefono($request);
    }
}
