<?php

namespace App\Http\Controllers;

use App\Models\Archivos;
use App\Models\Persona;
use App\Models\Usuario;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class UsuarioController extends Controller
{
    public function login(Request $request){

        return response()->json("Implementar metodo de login");
        
    }

    public function register (Request $request){
       
        $validator = Validator::make($request->all(), [
            'cedula' => 'required|unique:personas',
            'nombre' => 'required',
            'correo' => 'required',
            'contraseña' => 'required',
            'numerocuenta' => 'required',
            'id_provincia'   => 'required',
            'id_canton' => 'required',
            'id_distrito' => 'required',
            'id_barrio' => 'required',
            'otrassenas' => 'required'

        ]);
 
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()],422);
        } 
        
        try{
          // Creamos la nueava persons
            $nuevapersona =  Persona::create([
            'cedula' => $request->cedula,
            'nombre' => $request->nombre,
            'id_provincia' => $request->id_provincia,
            'id_canton' => $request->id_canton,
            'id_distrito' => $request->id_distrito,
            'id_barrio' => $request->id_barrio,
            'otrassenas' => $request->otrassenas
           ]);

           $imagenperfil = $request->file('imagenperfil'); //imagen de perfil
           $archivopdf = $request->file('archivo'); //archivopdf 

           // creamos el usuario apartir de los datos de persona metidos
           $nuevousuario = new Usuario();
           $nuevousuario-> usuario = $request->correo;
           $nuevousuario-> contraseña = Hash::make($request->contraseña);
           $nuevousuario->id_persona = $nuevapersona->id;
           $nuevousuario->rol = 1; 
           $nuevousuario->correo = $request->correo; 

           if($imagenperfil->getClientOriginalExtension() =='jpg'){
            $nuevousuario->imagen = file_get_contents($imagenperfil->getRealPath());
           } 
           $nuevousuario->save();

           if($archivopdf && $archivopdf->getClientOriginalExtension() =='pdf'){
             $archivo  = Archivos::create([
                'nombre' => $request->cedula, 
                'tipo' =>$archivopdf->getClientOriginalExtension(),
                'file' => file_get_contents($archivopdf->getRealPath()),
                'id_persona' => $nuevapersona->id
             ]);
           }
           dd($nuevapersona, $nuevousuario);
           return response()->json(['persona'=> $nuevapersona, 'usuario' =>$nuevousuario, ],200);

        } catch(Exception $e){
            return response()->json(['message' => $e->getMessage()],500);
        }
 
    }
}
