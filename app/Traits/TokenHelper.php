<?php

namespace App\Traits;

use Carbon\Carbon;

trait TokenHelper
{
    // Crear token para un usuario
    public function createToken($user)
    {
        $tokenResult = $user->createToken('Laravel', ['*']); // Crea el token

        // Expiración del token (por ejemplo, 1 hora desde ahora)
        $expiration = Carbon::now()->addSeconds(config('passport.tokensExpireIn'));

        // Regresar el token y su expiración
        return [
            'access_token' => $tokenResult->accessToken,
            'expires_at' => $expiration->toDateTimeString(), // Fecha de expiración del token
        ];
    }

    // Revocar token de un usuario
    public function revokeToken($user)
    {
        $user->tokens->each(function ($token) {
            $token->delete();
        });
    }
}
