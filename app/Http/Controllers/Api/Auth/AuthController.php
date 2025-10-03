<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\TokenHelper;
use App\Traits\ValidatorTrait;
use App\Traits\RolePermissions;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Traits\HasRoles;

class AuthController
{
    use RolePermissions, ApiResponseTrait, TokenHelper, ValidatorTrait, HasRoles;


    public function register(Request $request)
    {
        // Validación de datos
        $validation = $this->validateRequest($request, [
            'name'      => 'required|string|max:255|unique:users',
            'last_name' => 'required|string|max:255|unique:users',
            'username'  => 'required|string|max:255|unique:users',
            'email'     => 'nullable|string|email|max:255|unique:users',
            'password'  => 'required|string',
        ]);

        if ($validation->fails()) {
            return $this->validationErrorResponse($validation->errors());
        }

        // Crear usuario
        $user = User::create([
            'name'      => $request->name,
            'last_name' => $request->last_name,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
        ]);

        // Asignar rol por defecto
        $this->assignRoleToUser($user, 'Estudiante');

        // Crear token JWT
        $token = JWTAuth::fromUser($user);

        return $this->successResponse([
            'token' => $token,
            'user'  => $user->only(['id', 'username', 'email']),
            'roles' => $user->getRoleNames(),
        ], 'Usuario registrado correctamente', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'], // email o username
            'password' => ['required', 'string'],
        ]);

        // aceptar email o username
        $user = User::where('email', $credentials['login'])
            ->orWhere('username', $credentials['login'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['message' => 'No se pudo crear el token'], 500);
        }

        return response()->json([
            'message' => 'Inicio de sesión correcto',
            'data'    => [
                'token'       => $token,
                'expires_at'  => now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
                'user'        => $user->only(['id', 'name', 'last_name', 'username', 'email']),
                'roles'       => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ]);
    }

    public function getCurrentUser(Request $request)
    {
        try {
            // Intentar obtener token del header
            $token = $request->bearerToken();

            if (!$token) {
                // No hay token, no autorizado
                return $this->error('No autorizado - no hay token', 401);
            }

            // Intentar autenticar usuario con el token recibido
            if (!$user = JWTAuth::setToken($token)->authenticate()) {
                return $this->error('No autorizado - usuario no encontrado', 401);
            }

            // Token y usuario válidos, responder con datos y token
            return $this->successResponse([
                'token' => $token,
                'username' => $user->only(['id', 'username', 'email', 'name', 'last_name']),
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ], 'Sesión activa', 200);
        } catch (TokenInvalidException $e) {
            return $this->error('Token expirado, inicia sesión de nuevo', 401);
        } catch (TokenInvalidException $e) {
            return $this->error('Token inválido', 401);
        } catch (\Exception $e) {
            // Cualquier otro error
            return $this->error('No autorizado', 401);
        }
    }

    public function perfil(Request $request)
    {
        $user = $request->user();
        return $this->successResponse([
            'username' => $user->username,
            'email' => $user->email,
        ], 'Datos del usuario obtenidos correctamente');
    }


    public function logout(Request $request)
    {
        try {
            // Revocar el token del usuario
            JWTAuth::invalidate(JWTAuth::getToken());
            return $this->successResponse([], 'Sesión cerrada correctamente', 200);
        } catch (\Exception $e) {
            Log::error('Error al cerrar sesión: ' . $e->getMessage());
            return $this->error('No se pudo cerrar sesión. Intenta de nuevo más tarde.', 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->error('Usuario no encontrado', 404);
            }

            // Reglas de validación base
            $validationRules = [
                'name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'code' => 'sometimes|nullable|string|max:255',
                'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'imagen_url' => 'sometimes|nullable|string|max:500', // Validar la URL de la imagen
            ];

            // Si se está cambiando la contraseña, agregar validaciones adicionales
            if ($request->filled('new_password')) {
                $validationRules['current_password'] = 'required|string';
                $validationRules['new_password'] = 'required|string|min:6|max:255';
                $validationRules['confirm_password'] = 'required|string|same:new_password';
            }

            // Validar los datos
            $validation = $this->validateRequest($request, $validationRules);

            if ($validation->fails()) {
                return $this->validationErrorResponse($validation->errors());
            }

            // Verificar contraseña actual si se está cambiando la contraseña
            if ($request->filled('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return $this->error('La contraseña actual es incorrecta', 422);
                }
            }

            // Preparar datos para actualizar
            $updateData = [];

            if ($request->filled('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->filled('last_name')) {
                $updateData['last_name'] = $request->last_name;
            }

            if ($request->has('code')) {
                $updateData['code'] = $request->code;
            }

            if ($request->filled('username')) {
                $updateData['username'] = $request->username;
            }

            if ($request->filled('email')) {
                $updateData['email'] = $request->email;
            }

            if ($request->has('imagen_url')) {
                $updateData['imagen_url'] = $request->imagen_url;
            }

            if ($request->filled('new_password')) {
                $updateData['password'] = bcrypt($request->new_password);
            }

            // Actualizar el usuario
            $user->update($updateData);

            // Generar nuevo token si se cambió la contraseña
            $newToken = null;
            if ($request->filled('new_password')) {
                try {
                    $newToken = JWTAuth::fromUser($user);
                } catch (JWTAuth $e) {
                }
            }

            // Preparar respuesta
            $responseData = [
                'user' => $user->only(['id', 'name', 'last_name', 'code', 'username', 'email', 'imagen_url']),
            ];

            if ($newToken) {
                $responseData['token'] = $newToken;
                $responseData['expires_at'] = now()->addMinutes(config('jwt.ttl'))->toDateTimeString();
            }

            return $this->successResponse($responseData, 'Perfil actualizado correctamente', 200);
        } catch (TokenExpiredException $e) {
            return $this->error('Token expirado, inicia sesión de nuevo', 401);
        } catch (TokenInvalidException $e) {
            return $this->error('Token inválido', 401);
        } catch (\Exception $e) {
            Log::error('Error al actualizar perfil: ' . $e->getMessage());
            return $this->error('Error al actualizar el perfil. Intenta de nuevo más tarde.', 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('users', 'public');
            $url = asset('storage/' . ltrim($path, '/'));
            return response()->json(['url' => $url]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
