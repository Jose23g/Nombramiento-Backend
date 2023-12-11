<?php

namespace App\Http\Controllers;

use App\Models\Archivos;
use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Http\Request;

class ArchivosController extends Controller
{
    public function obtenga(Request $request)
    {
        return response()->json($request->user()->archivo);
    }
    public function guardarimagen($usuarioId, $imagenPerfil)
    {
        $usuario = Usuario::find($usuarioId);

        if (!$usuario) {
            return response()->json(['Error' => 'Usuario no encontrado'], 400);
        }
        try {
            if ($imagenPerfil) {
                $usuario->imagen = $imagenPerfil;
                $usuario->save();
            } else {
                return response()->json(['Error' => 'Revise el contenido o el formato de la imagen'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function guardardocumento($personaId, $documento)
    {
        $persona = Persona::find($personaId);

        if (!$persona) {
            return response()->json(['Error' => 'Persona no encontrado'], 400);
        }
        try {
            if ($documento) {
                try {
                    $nombreArchivo = $persona->nombre . '_DocumentosAsociados'; // Nombre del archivo
                    Archivos::create([
                        'nombre' => $nombreArchivo,
                        'file' => $documento,
                        'persona_id' => $persona->id,
                    ]);
                    // dd($pdfBase64);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            } else {
                return response()->json(['Error' => 'Revise el contenido o el formato del pdf'], 400);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
