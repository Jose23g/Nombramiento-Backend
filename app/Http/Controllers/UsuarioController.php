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
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
    
            if ($imagenPerfil->isValid() && in_array($imagenPerfil->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                // Obtener el contenido de la imagen
                $contenidoImagen = file_get_contents($imagenPerfil->getPathname());
    
                // Convertir el contenido de la imagen a Base64
                $imagenBase64 = base64_encode($contenidoImagen);
    
                // Crear el usuario a partir de los datos de persona
                $nuevoUsuario = new Usuario();
                $nuevoUsuario->usuario = $request->correo;
                $nuevoUsuario->contrasena = Hash::make($request->contrasena);
                $nuevoUsuario->id_persona = $nuevaPersona->id;
                $nuevoUsuario->id_rol = 1;
                $nuevoUsuario->correo = $request->correo;
                $nuevoUsuario->imagen = $imagenBase64; // Almacenar la imagen en formato Base64
                $nuevoUsuario->save();
    
                DB::commit();
    
                return response()->json(['persona' => $nuevaPersona, 'usuario' => $nuevoUsuario], 200);
            } else {
                DB::rollback();
                return response()->json(['error' => 'La imagen de perfil no es válida o no es un formato admitido (jpg, jpeg, png)'], 400);
            }
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


            return response()->json([
                'Usuario' => [
                    'correo' => $usuario->correo,
                    'nombre' => $persona->nombre,
                    'cedula' => $persona->cedula,
                    'Telefono' => $telefonos,
                    'cuentabancaria' => 'No implementado aun',
                    'foto_perfil' => 'no implementado',
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

}