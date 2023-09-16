<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Archivos;
use App\Models\Persona;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Passport;

class UsuarioController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'correo' => 'required',
                'contrasena' => 'required'
            ]);

            $usuario = Usuario::where('correo', $request->input('correo'))->first();

            if (!$usuario) {
                return response()->json(['Error' => 'Credenciales incorrectas'], 401);
            }

            if (Hash::check($request->input('contrasena'), $usuario->contrasena)) {
                
                Passport::actingAs($usuario);
                $token = $usuario->createToken('MyAppToken')->accessToken;
                $persona = Persona::where('id', $usuario->id_persona)->select('nombre')->first();
                return response()->json(['Persona'=>$persona ,'token' => $token], 200);

            } else {
                return response()->json(['error' => 'Credenciales incorrectas'], 401);
            }

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'cedula' => 'required|unique:personas',
            'nombre' => 'required',
            'correo' => 'required',
            'contrasena' => 'required',
            'numerocuenta' => 'required',
            'id_provincia' => 'required',
            'id_canton' => 'required',
            'id_distrito' => 'required',
            'id_barrio' => 'required',
            'otrassenas' => 'required'

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()],422);
        } 
        DB::beginTransaction();
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
           $nuevousuario-> contrasena = Hash::make($request->contrasena);
           $nuevousuario->id_persona = $nuevapersona->id;
           $nuevousuario->id_rol = 1; 
           $nuevousuario->correo = $request->correo; 

           if( $imagenperfil && $imagenperfil->getClientOriginalExtension() =='jpg'){
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
           DB::commit();
           return response()->json(['persona'=> $nuevapersona, 'usuario' =>$nuevousuario, ],200);
          
        } catch(Exception $e){
            DB::rollback();
            return response()->json(['message' => $e->getMessage()],500);
        }

    }

    
}