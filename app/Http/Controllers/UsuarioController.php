<?php

namespace App\Http\Controllers;

use App\Models\Barrio;
use App\Models\Canton;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\Telefono;
use Exception;
use App\Models\Archivos;
use App\Models\Persona;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    public function login(Request $request)
    {

      //  dd($request);
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
                $rolScope = $usuario->rol()->first()->nombre;
                $token = $usuario->createToken('MyAppToken', [$rolScope])->accessToken;

                $persona = Persona::where('id', $usuario->id_persona)->select('nombre')->first();
                return response()->json(['Persona' => $persona, 'token' => $token], 200);

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
            'cuenta' => 'required',
            'id_provincia' => 'required',
            'id_canton' => 'required',
            'id_distrito' => 'required',
            'id_barrio' => 'required',
            'otrassenas' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Crear la nueva persona
            $nuevaPersona = Persona::create([
                'cedula' => $request->cedula,
                'nombre' => $request->nombre,
                'cuenta' => $request->cuenta,
                'id_banco' => $request->id_banco,
                'id_provincia' => $request->id_provincia,
                'id_canton' => $request->id_canton,
                'id_distrito' => $request->id_distrito,
                'id_barrio' => $request->id_barrio,
                'otrassenas' => $request->otrassenas,
            ]);

            $imagenPerfil = $request->file('imagenperfil'); // Imagen de perfil
            $documento = $request->file('documento'); // Imagen de perfil

            // Crear el usuario a partir de los datos de persona
            $nuevoUsuario = new Usuario();
            $nuevoUsuario->usuario = $request->correo;
            $nuevoUsuario->contrasena = Hash::make($request->contrasena);
            $nuevoUsuario->id_persona = $nuevaPersona->id;
            $nuevoUsuario->id_rol = 1;
            $nuevoUsuario->correo = $request->correo;
            $nuevoUsuario->save();
            DB::commit();

            if ($imagenPerfil !== null) {
                $imagen = app()->make(ArchivosController::class);
                try {
                    $resultado = $imagen->guardarimagen($nuevoUsuario->id, $imagenPerfil);
                } catch (Exception $e) {
                    return response(['mensaje' => $e->getMessage()]);
                }
            }

            if ($documento !== null) {
                try {
                    $pdf = app()->make(ArchivosController::class);
                    //dd($nuevaPersona->id);
                    $resultado = $pdf->guardardocumento($nuevaPersona->id, $documento);
                } catch (Exception $e) {
                    return response(['mensaje' => $e->getMessage()]);
                }
            }

            return response()->json(['Message' => 'Se ha registrado con éxito','persona' => $nuevaPersona, 'usuario' => $nuevoUsuario], 200);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function obtenerUsuario(Request $request)
    {
        $usuario = $request->user();

        if ($usuario) {
            $persona = Persona::find($usuario->id_persona);
            $archivo = Archivos::where('id_persona', $persona->id_persona);
            $telefonos = Telefono::where('id_persona', $persona->id_persona);
            $barrio = Barrio::select('id', 'nombre')->find($persona->id_barrio);
            $distrito = Distrito::select('id', 'nombre')->find($persona->id_distrito);
            $canton = Canton::select('id', 'nombre')->find($persona->id_canton);
            $provincia = Provincia::select('id', 'nombre')->find($persona->id_provincia);

            $imagenCodificada = $usuario->imagen;
            $imagenDecodificada = base64_decode($imagenCodificada);


            $response = response($imagenDecodificada, 200);

            $response->header('Content-Type', 'image/jpg');

            return response()->json([
                'Usuario' => [
                    'correo' => $usuario->correo,
                    'nombre' => $persona->nombre,
                    'cedula' => $persona->cedula,
                    'Telefono' => $telefonos,
                    'cuentabancaria' => 'No implementado aun',
                    'foto_perfil' => $usuario->imagen,
                    'archivo' => $archivo,
                    'direccion' => [
                        'provincia' => $provincia,
                        'canton' => $canton,
                        'distrito' => $distrito,
                        'barrio' => $barrio,
                        'otrassenas' => $persona->otrassenas,
                    ]
                ]
            ], 200);
        } else {
            return response()->json(['Error' => 'Usuario no autenticado'], 401);
        }
    }

    public function editeUsuario(Request $request)
    {
        $usuario = Usuario::find($request->id);
        $usuario->imagen = base64_encode($request->file('imagen'));
        $usuario->save();
        $request->id = $usuario->id_persona;
        editePersona($request);
    }

    public function editePersona(Request $request)
    {
        $persona = Persona::find($request->id);
        $persona->cuenta = $request->cuenta;
        $persona->nombre = $request->nombre;
        $persona->otrassenas = $request->otrassenas;
        $persona->id_banco = $request->id_banco;
        $persona->id_distrito = $request->id_distrito;
        $persona->id_barrio = $request->id_barrio;
        $persona->id_provincia = $request->id_provincia;
        $persona->id_canton = $request->id_canton;
        $persona->save();
    }

    public function validartoken(Request $request){
     /*    $usuario = $request->user();
      if($usuario!== null){
        return response()->json(['status' => 'true', 'scope' => $usuario->getScopes() ],200);
      } */

      $user = Auth::user(); // Obtiene el usuario autenticado
      $scopes = $request->user()->token()->scopes; // Obtiene los scopes del token del usuario
       
      if ($user !== null) {
          return response()->json(['status' => 'true', 'scopes' => $scopes], 200);
      }
    } 
}