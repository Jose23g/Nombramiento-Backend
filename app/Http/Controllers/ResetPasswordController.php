<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function notice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email|exists:usuarios,correo',
        ], [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute ingresado no existe en la tabla de usuarios.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        Password::sendResetLink($request->only('correo'));

        if (!Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Algo ha salido mal'], 400);
        }

        return response()->json(['message' => 'Se ha enviado el correo de recuperacion de contraseña'], 200);
    }

    public function recover(Request $request): RedirectResponse
    {
        return redirect(env('FRONT_URL') . '/password/reset' . '?id=' . $request->route('id') . '&hash=' . $request->route('hash'));
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email|exists:usuarios,correo',
            'contrasena' => 'required|confirmed',
            'token' => 'required',
        ], [
            'required' => 'El campo :attribute es requerido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $estado = Password::reset(
            ['correo' => $request->correo, 'password' => $request->contrasena, 'token' => $request->token],
            function ($usuario, $contrasena) {
                $usuario->forceFill([
                    'contrasena' => Hash::make($contrasena),
                ]);

                $usuario->save();

                event(new PasswordReset($usuario));
            }
        );
        switch ($estado) {
            case Password::PASSWORD_RESET:
                return response()->json(['message' => 'Se ha restablecido la contraseña'], 200);
            case Password::INVALID_TOKEN:
                return response()->json(['message' => 'Token inválido'], 400);
            case Password::INVALID_USER:
                return response()->json(['message' => 'Usuario no encontrado'], 400);
            case Password::RESET_THROTTLED:
                return response()->json(['message' => 'Demasiados intentos de restablecimiento. Por favor, espere.'], 400);
            default:
                return response()->json(['message' => 'Algo ha salido mal'], 400);
        }
    }
}
