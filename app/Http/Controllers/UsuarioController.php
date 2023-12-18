<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Usuario;
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
        //  dd($request);
        try {
            $request->validate([
                'correo' => 'required',
                'contrasena' => 'required',
            ]);

            $usuario = Usuario::with(['rol', 'persona', 'carreras'])->where('correo', $request->input('correo'))->first();
           

            if (!$usuario) {
                return response()->json(['Error' => 'Credenciales incorrectas'], 401);
            }
            if (Hash::check($request->input('contrasena'), $usuario->contrasena)) {
                Passport::actingAs($usuario);
                $rolScope = $usuario->rol->nombre;
                $token = $usuario->createToken('MyAppToken', [$rolScope])->accessToken;
                $persona = $usuario->persona->nombre;
                $carrera = $usuario->carreras->first();
                if ($carrera) {
                    $carrera = $carrera->nombre;
                }

                return response()->json(['nombre' => $persona, 'carrera' => $carrera, 'token' => $token, 'scope' => $rolScope], 200);
            } else {
                return response()->json(['error' => 'Credenciales incorrectas'], 401);
            }
        } catch (\Exception $e) {
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
            'banco_id' => 'required',
            'provincia_id' => 'required',
            'canton_id' => 'required',
            'distrito_id' => 'required',
            'otras_senas' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
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
                'banco_id' => $request->banco_id,
                'provincia_id' => $request->provincia_id,
                'canton_id' => $request->canton_id,
                'distrito_id' => $request->distrito_id,
                'otras_senas' => $request->otras_senas,
            ]);

            $numeroTelefono = $request->numero;

            if ($numeroTelefono && strlen($numeroTelefono) === 8 && ctype_digit($numeroTelefono)) {
                $nuevotelefono = Telefono::create([
                    'persona_id' => $nuevaPersona->id,
                    'personal' => $request->numero,
                ]);
            }

            $imagenPerfil = $request->imagen_perfil; // Imagen de perfil
            $documento = $request->archivos; // pdf de la persona

            // Crear el usuario a partir de los datos de persona
            $nuevoUsuario = Usuario::create([
                'rol_id' => 1,
                'persona_id' => $nuevaPersona->id,
                'estado_id' => 5,
                'correo' => $request->correo,
                'otro_correo' => $request->otro_correo,
                'contrasena' => Hash::make($request->contrasena),
            ]);
            DB::commit();

            if ($imagenPerfil) {
                $imagen = app()->make(ArchivosController::class);
                try {
                    $resultado = $imagen->guardarimagen($nuevoUsuario->id, $imagenPerfil);
                } catch (\Exception $e) {
                    return response(['mensaje' => $e->getMessage()]);
                }
            }

            if ($documento) {
                try {
                    $pdf = app()->make(ArchivosController::class);
                    // dd($nuevaPersona->id);
                    $resultado = $pdf->guardardocumento($nuevaPersona->id, $documento);
                } catch (\Exception $e) {
                    return response(['mensaje' => $e->getMessage()]);
                }
            }

            return response()->json(['Message' => 'Se ha registrado con éxito', 'persona' => $nuevaPersona, 'usuario' => $nuevoUsuario], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function obtenerUsuario(Request $request)
    {
        $usuario = $request->user();

        if ($usuario) {
            $persona = $usuario->persona;
            $archivo = $persona->archivos;
            $telefono = $persona->telefono;
            $distrito = $persona->distrito;
            $canton = $persona->canton;
            $provincia = $persona->provincia;
            $banco = $persona->banco;

            $imagenCodificada = $usuario->imagen;
            $imagenDecodificada = base64_decode($imagenCodificada);

            $response = response($imagenDecodificada, 200);

            $response->header('Content-Type', 'image/jpg');

            return response()->json([
                'Usuario' => [
                    'correo' => $usuario->correo,
                    'otro_correo' => $usuario->otro_correo,
                    'nombre' => $persona->nombre,
                    'cedula' => $persona->cedula,
                    'telefono' => $telefono,
                    'cuenta_bancaria' => $persona->cuenta,
                    'foto_perfil' => $usuario->imagen,
                    'archivo' => $archivo,
                    'banco' => $banco,
                    'direccion' => [
                        'provincia' => $provincia,
                        'canton' => $canton,
                        'distrito' => $distrito,
                        'otras_senas' => $persona->otras_senas,
                    ],
                ],
            ], 200);
        } else {
            return response()->json(['Error' => 'Usuario no autenticado'], 401);
        }
    }

    public function editeUsuario(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $usuario = Usuario::find($user_id);
            $usuario->otro_correo = $request->otro_correo;
            if ($request->imagen) {
                $usuario->imagen = $request->imagen;
            }
            $usuario->save();
            $request->merge(['persona_id' => $usuario->persona_id]);
            app(PersonaController::class)->editePersona($request);

            return response()->json($request->all());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function validartoken(Request $request)
    {
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

    public function obtengaElCoordinadorActual(Request $request)
    {
        $carrera = $request->user()->carreras->first();
        $persona = $request->user()->persona;

        return ['coordinador_id' => $request->user()->id, 'nombre' => $persona->nombre, 'carrera_id' => $carrera->id, 'carrera_nombre' => $carrera->nombre];
    }
    public function obtengaElProfesorActual(Request $request)
    {
        $usuario = $request->user();
        $persona = $usuario->persona;
        $telefono = $persona->telefono;
        if ($telefono) {
            return ['profesor_id' => $request->user()->id, 'nombre' => $persona->nombre, 'cedula' => $persona->cedula, 'correos' => ['personal' => $usuario->correo, 'trabajo' => $usuario->otro_correo], 'telefonos' => ['personal' => $telefono->personal, 'trabajo' => $telefono->trabajo]];
        }
        return ['profesor_id' => $request->user()->id, 'nombre' => $persona->nombre, 'cedula' => $persona->cedula, 'correos' => ['personal' => $usuario->correo, 'trabajo' => $usuario->otro_correo], 'telefonos' => ['personal' => null, 'trabajo' => null]];
    }

    public function misCarreras(Request $request){
        $usuario = $request->user();
        $carreras = $usuario->carreras;

        if(!$carreras){
            return response()->json('Usuario sin carrera asignada', 400);
        }

        return response()->json($carreras, 200);
    }
}
