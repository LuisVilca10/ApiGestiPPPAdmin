<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UploadPhotoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\TokenHelper;
use App\Traits\RolePermissions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController
{
    use RolePermissions, ApiResponseTrait, TokenHelper;

    private function userArray(User $user): array
    {
        return [
            'id'                   => $user->id,
            'name'                 => $user->name,
            'last_name'            => $user->last_name,
            'code'                 => $user->code,
            'username'             => $user->username,
            'email'                => $user->email,
            'photo_url'            => $user->photo_url,
            'academic_cycle'       => $user->academic_cycle,
            'hours_of_practice'    => $user->hours_of_practice,
            'must_change_password' => (bool) $user->must_change_password,
            'email_verified'       => $user->hasVerifiedEmail(),
            'email_verified_at'    => $user->email_verified_at?->toISOString(),
        ];
    }

    public function register(RegisterRequest $request)
    {
        $cacheKey = "email_verify:{$request->email}";
        $stored   = Cache::get($cacheKey);

        if (!$stored) {
            return $this->errorResponse('El código ha expirado. Solicita uno nuevo.', 422);
        }

        if ($stored !== $request->verification_code) {
            return $this->errorResponse('Código de verificación incorrecto.', 422);
        }

        // Código válido — crear cuenta ya verificada
        Cache::forget($cacheKey);
        Cache::forget("email_code_throttle:{$request->email}");

        $user = User::create([
            'name'              => $request->name,
            'last_name'         => $request->last_name,
            'code'              => $request->code,
            'username'          => $request->username,
            'email'             => $request->email,
            'password'          => bcrypt($request->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Estudiante');

        $token = JWTAuth::fromUser($user);

        return $this->successResponse([
            'token' => $token,
            'user'  => $this->userArray($user),
            'roles' => $user->getRoleNames(),
        ], 'Cuenta creada correctamente.', 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (! $user = auth('api')->setTTL(config('jwt.ttl'))->attempt($credentials)) {
            return $this->errorResponse('Credenciales inválidas', 401);
        }

        $user  = auth('api')->user();
        $token = JWTAuth::fromUser($user);

        return $this->successResponse([
            'token'       => $token,
            'expires_at'  => now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'user'        => $this->userArray($user),
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 'Usuario iniciado sesión correctamente', 200);
    }

    public function getCurrentUser(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return $this->errorResponse('No autorizado - no hay token', 401);
            }

            if (!$user = JWTAuth::setToken($token)->authenticate()) {
                return $this->errorResponse('No autorizado - usuario no encontrado', 401);
            }

            return $this->successResponse([
                'token'       => $token,
                'user'        => $this->userArray($user),
                'roles'       => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ], 'Sesión activa', 200);
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Token expirado, inicia sesión de nuevo', 401);
        } catch (TokenInvalidException $e) {
            return $this->errorResponse('Token inválido', 401);
        } catch (\Exception $e) {
            return $this->errorResponse('No autorizado', 401);
        }
    }

    public function perfil(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'user'        => $this->userArray($user),
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 'Datos del usuario obtenidos correctamente');
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->successResponse([], 'Sesión cerrada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al cerrar sesión: ' . $e->getMessage());

            return $this->errorResponse('No se pudo cerrar sesión. Intenta de nuevo más tarde.', 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->errorResponse('Usuario no encontrado', 404);
            }

            if ($request->filled('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return $this->errorResponse('La contraseña actual es incorrecta', 422);
                }
            }

            $updateData = [];

            if ($request->filled('name'))       $updateData['name']       = $request->name;
            if ($request->filled('last_name'))  $updateData['last_name']  = $request->last_name;
            if ($request->has('code'))          $updateData['code']        = $request->code;
            if ($request->filled('username'))   $updateData['username']   = $request->username;
            if ($request->filled('email'))      $updateData['email']      = $request->email;
            if ($request->has('photo_url'))     $updateData['photo_url']  = $request->photo_url;

            if ($request->filled('new_password')) {
                $updateData['password'] = bcrypt($request->new_password);
            }

            $user->update($updateData);

            $newToken    = null;
            $responseData = [
                'user' => $this->userArray($user),
            ];

            if ($request->filled('new_password')) {
                $newToken = JWTAuth::fromUser($user);
                $responseData['token']      = $newToken;
                $responseData['expires_at'] = now()->addMinutes(config('jwt.ttl'))->toDateTimeString();
            }

            return $this->successResponse($responseData, 'Perfil actualizado correctamente');
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Token expirado, inicia sesión de nuevo', 401);
        } catch (TokenInvalidException $e) {
            return $this->errorResponse('Token inválido', 401);
        } catch (\Exception $e) {
            Log::error('Error al actualizar perfil: ' . $e->getMessage());

            return $this->errorResponse('Error al actualizar el perfil. Intenta de nuevo más tarde.', 500);
        }
    }

    public function changePassword(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return $this->errorResponse('Usuario no autenticado', 401);
        }

        $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->update([
            'password'             => bcrypt($request->new_password),
            'must_change_password' => false,
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->successResponse([
            'token'      => $token,
            'expires_at' => now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'user'       => $this->userArray($user),
        ], 'Contraseña actualizada correctamente');
    }

    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return $this->successResponse([
                'token'      => $newToken,
                'expires_at' => now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            ], 'Token renovado correctamente');
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Token expirado, inicia sesión de nuevo', 401);
        } catch (TokenInvalidException $e) {
            return $this->errorResponse('Token inválido', 401);
        } catch (\Exception $e) {
            return $this->errorResponse('No se pudo renovar el token', 401);
        }
    }

    public function uploadPhoto(UploadPhotoRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return $this->errorResponse('Usuario no autenticado', 401);
        }

        // getRawOriginal devuelve la ruta relativa real (ej: "users/foto.jpg")
        $rawPath = $user->getRawOriginal('photo_url');
        if ($rawPath && Storage::disk('public')->exists($rawPath)) {
            Storage::disk('public')->delete($rawPath);
        }

        $path = $request->file('photo')->store('users', 'public');

        $user->update(['photo_url' => $path]);

        return $this->successResponse(
            ['photo_url' => Storage::url($path)],
            'Foto actualizada correctamente'
        );
    }
}
