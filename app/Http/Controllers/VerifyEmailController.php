<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VerifyEmailController extends Controller
{

    public function verify(Request $request): RedirectResponse
    {
        $usuario = Usuario::find($request->route('id'));

        if ($usuario->hasVerifiedEmail()) {
            return redirect(env('FRONT_URL') . '/email/verify/already-success');
        }

        if ($usuario->markEmailAsVerified()) {
            event(new Verified($usuario));
        }

        return redirect(env('FRONT_URL') . '/email/verify/success');
    }

    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Se ha reenviado el correo']);
    }
}
