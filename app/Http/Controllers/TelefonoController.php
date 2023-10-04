<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Telefono;
class TelefonoController extends Controller
{
    public function editeTelefono(Request $request)
    {
        $telefono = Telefono::where('id_persona', $request->id_persona)->first();
        if(!$telefono){
            Telefono::create([
                    'id_persona' => $request->persona_id,
                    'personal' => $request->tel_personal,
                    'trabajo' => $request->tel_trabajo,
            ]);
            return;
        }
        $telefono->personal = $request->tel_personal;
        $telefono->trabajo = $request->tel_trabajo;
        $telefono->save();
    }
}
