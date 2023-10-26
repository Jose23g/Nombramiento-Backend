<?php

namespace App\Http\Controllers;

use App\Models\Telefono;
use Illuminate\Http\Request;

class TelefonoController extends Controller
{
    public function editeTelefono(Request $request)
    {
        $telefono = Telefono::where('persona_id', $request->persona_id)->first();
        if (!$telefono) {
            Telefono::create([
                    'persona_id' => $request->persona_id,
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
