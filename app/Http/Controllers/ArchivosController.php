<?php

namespace App\Http\Controllers;
use App\Models\Persona;
use App\Models\Usuario;
use App\Models\Archivos;
use Illuminate\Http\Request;

class ArchivosController extends Controller
{
    public function guardarimagen($id_usuario,$imagenperfil){
        
        $usuario = Usuario::find($id_usuario);

        if (!$usuario) {
            return response()->json(['Error' => 'Usuario no encontrado'], 400);
        }
       try{

         if ($imagenPerfil->isValid() && in_array($imagenPerfil->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
           
           $contenidoImagen = file_get_contents($imagenPerfil->getPathname());
           $imagenBase64 = base64_encode($contenidoImagen);
           $usuario->imagen = $imagenBase64;
           $usuario->save();
       
        }else{
           return response()->json(['Error' => 'Revise el contenido o el formato de la imagen'], 400);
        } 

       }catch(Exception $e){
           return response()->json(['message' => $e->getMessage()], 500);
       }
       
   }

   public function guardardocumento($id_persona, $documento){
   
    $persona = Persona::find($id_persona);

    if (!$persona) {
        return response()->json(['Error' => 'Persona no encontrado'], 400);
    }
   try{

     if ($documento->isValid() && in_array($documento->getClientOriginalExtension(), ['pdf'])) {
       
        $contenidoPDF = file_get_contents($documento->getPathname());
        $pdfBase64 = base64_encode($contenidoPDF);
       
        $nuevoarchivo = Archivos::create([
            'nombre' => $documento->getClientOriginalName(),
            'tipo' => $documento->getClientOriginalExtension(),
            'file' => $pdfBase64,
            'id_persona' => $persona->$id_persona
        ]);
         
   
    }else{
       return response()->json(['Error' => 'Revise el contenido o el formato del pdf'], 400);
    } 

   }catch(Exception $e){
       return response()->json(['message' => $e->getMessage()], 500);
   }
   }

}
