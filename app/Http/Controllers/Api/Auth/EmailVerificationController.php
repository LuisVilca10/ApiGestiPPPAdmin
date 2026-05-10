<?php

namespace App\Http\Controllers\Api\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class EmailVerificationController
{
    use ApiResponseTrait;

    // Envía un código de 6 dígitos al correo ANTES del registro (ruta pública)
    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = $request->email;

        if (User::where('email', $email)->exists()) {
            return $this->errorResponse('Este correo ya está registrado.', 422);
        }

        // Throttle: no reenviar si aún queda código vigente con menos de 1 min de antigüedad
        $throttleKey = "email_code_throttle:{$email}";
        if (Cache::has($throttleKey)) {
            return $this->errorResponse('Espera un momento antes de solicitar otro código.', 429);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("email_verify:{$email}", $code, now()->addMinutes(10));
        Cache::put($throttleKey, true, now()->addMinute());

        Mail::to($email)->send(new VerificationCodeMail($code));

        return $this->successResponse([], 'Código enviado a ' . $email . '. Válido por 10 minutos.');
    }

    // Envía (o reenvía) el correo de verificación al usuario autenticado
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('El correo ya está verificado.', 422);
        }

        $user->sendEmailVerificationNotification();

        return $this->successResponse([], 'Correo de verificación enviado a ' . $user->email);
    }

    // Verifica el correo cuando el usuario hace clic en el enlace del email
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Validar firma del enlace
        if (!URL::hasValidSignature($request)) {
            return $this->errorResponse('El enlace de verificación es inválido o ha expirado.', 403);
        }

        // Validar que el hash coincida con el correo del usuario
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return $this->errorResponse('El enlace no corresponde a este usuario.', 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(
                ['email_verified_at' => $user->email_verified_at],
                'El correo ya estaba verificado.'
            );
        }

        $user->markEmailAsVerified();

        return $this->successResponse(
            ['email_verified_at' => $user->email_verified_at],
            '¡Correo verificado correctamente! Ya puedes usar tu cuenta.'
        );
    }

    // Verifica el correo desde el enlace del email y retorna una página HTML
    public function verifyFromEmail(Request $request, $id, $hash): View
    {
        $user = User::find($id);

        // Usuario no encontrado
        if (!$user) {
            return view('email.verify-result', [
                'status'  => 'invalid',
                'message' => 'El enlace de verificación es inválido o ha expirado.',
            ]);
        }

        // Firma del URL inválida o expirada
        if (!URL::hasValidSignature($request)) {
            return view('email.verify-result', [
                'status'  => 'invalid',
                'message' => 'El enlace de verificación ha expirado. Solicita uno nuevo desde la aplicación.',
            ]);
        }

        // Hash no coincide con el correo del usuario
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return view('email.verify-result', [
                'status'  => 'invalid',
                'message' => 'El enlace de verificación no corresponde a este usuario.',
            ]);
        }

        // Correo ya estaba verificado
        if ($user->hasVerifiedEmail()) {
            return view('email.verify-result', [
                'status'  => 'already_verified',
                'message' => 'Tu correo ya estaba verificado. Ya puedes usar tu cuenta.',
            ]);
        }

        // Marcar como verificado
        $user->markEmailAsVerified();

        return view('email.verify-result', [
            'status'  => 'success',
            'message' => '¡Correo verificado correctamente! Ya puedes usar tu cuenta.',
        ]);
    }

    // Consultar estado de verificación del usuario autenticado
    public function status(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'email'             => $user->email,
            'verified'          => $user->hasVerifiedEmail(),
            'email_verified_at' => $user->email_verified_at?->toISOString(),
        ]);
    }
}
