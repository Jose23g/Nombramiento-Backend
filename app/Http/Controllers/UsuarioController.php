<?php

namespace App\Http\Controllers;

use App\Models\Archivos;
use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Usuario;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|exists:usuarios,correo',
            'contrasena' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        if (Auth::attempt(['correo' => $request->correo, 'password' => $request->contrasena])) {
            $usuario = Auth::user();
            $resultado = app()->handle(Request::create('oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => env("CLIENT_ID"),
                'client_secret' => env("CLIENT_SECRET"),
                'username' => $request->correo,
                'password' => $request->contrasena,
                'scope' => $usuario->rol->nombre,
            ]));
            $respuesta = json_decode($resultado->getContent(), true);
            $carrera = $usuario->carreras->first() ? $usuario->carreras->first()->nombre : null;
            return response()->json(['nombre' => $usuario->persona->nombre, 'scope' => $usuario->rol->nombre, 'carrera' => $carrera, ...$respuesta, 'imagen' => $usuario->imagen], 200);
        }

        return response()->json(['message' => 'Credenciales Inválidas'], 402);
    }
    public function renueveElToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $resultado = app()->handle(Request::create('oauth/token', 'POST', [
            'grant_type' => 'refresh_token',
            'client_id' => env("CLIENT_ID"),
            'client_secret' => env("CLIENT_SECRET"),
            'refresh_token' => $request->refresh_token,
            'scope' => '',
        ]));
        $respuesta = json_decode($resultado->getContent(), true);
        return response()->json($respuesta, 200);
    }
    public function revoqueLosTokens(Request $request)
    {
        $request->user()->tokens->each(function ($token, $key) {
            if (!$token->revoked) {
                $this->revoqueAccessYRefreshTokens($token->id);
            }
        });
        return response()->json(['message' => 'All refresh tokens revoked successfully.'], 200);
    }
    protected function revoqueAccessYRefreshTokens($tokenId)
    {
        $tokenRepository = app('Laravel\Passport\TokenRepository');
        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

        $tokenRepository->revokeAccessToken($tokenId);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cedula' => 'required|unique:personas',
            'nombre' => 'required',
            'correo' => 'required|email|unique:usuarios',
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
                Telefono::create([
                    'persona_id' => $nuevaPersona->id,
                    'personal' => $request->numero,
                ]);
            }

            // Crear el usuario a partir de los datos de persona
            $nuevoUsuario = Usuario::create([
                'rol_id' => 1,
                'imagen' => $request->imagen_perfil,
                'persona_id' => $nuevaPersona->id,
                'estado_id' => 5,
                'correo' => $request->correo,
                'otro_correo' => $request->otro_correo,
                'contrasena' => Hash::make($request->contrasena),
            ]);

            $documento = $request->archivos; // pdf de la persona
            $request->merge(['persona' => $nuevaPersona]);
            if ($documento) {
                app()->make(TituloController::class)->guarde($request);
            }
            DB::commit();
            event(new Registered($nuevoUsuario));
            return response()->json(['Message' => 'Se ha registrado con éxito', 'persona' => $nuevaPersona, 'usuario' => $nuevoUsuario], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function obtengaUsuario(Request $request)
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

    public function misCarreras(Request $request)
    {
        $usuario = $request->user();
        $carreras = $usuario->carreras->map(function ($carrera) {
            return [
                'id' => $carrera->id,
                'nombre' => $carrera->nombre,
            ];
        });

        if (!$carreras) {
            return response()->json('Usuario sin carrera asignada', 400);
        }

        return response()->json($carreras, 200);
    }

    public function listar_todos_los_usuarios(Request $request)
    {
        $usuarios = Usuario::join('roles as r', 'usuarios.rol_id', '=', 'r.id')
            ->join('personas as p', 'usuarios.persona_id', '=', 'p.id')
            ->select('p.nombre as nombre', 'usuarios.correo as correo', 'r.nombre as rol', 'usuarios.id as id')
            ->get();

        $listadofinal = [];

        foreach ($usuarios as $usuario) {
            $user = Usuario::find($usuario->id);
            $carreras = $user->carreras()
                ->select('carreras.id', 'carreras.nombre')
                ->get();
            $listadofinal[] = (object) [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'carreras' => $carreras,
                'rol' => $usuario->rol,
            ];
        }

        return response()->json($listadofinal, 200);
    }

    public function EditarRolUsuario(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'usuario_id' => 'required|exists:usuarios,id',
                'rol_id' => 'required|exists:roles,id',
            ],
            [
                'exists' => 'no existe la referencia',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {

            $usuario = Usuario::find($request->usuario_id);
            $usuario->rol_id = $request->rol_id;
            $usuario->save();

            return response()->json(['message' => "Rol de " . $usuario->persona->nombre . " modificado con exito"], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
