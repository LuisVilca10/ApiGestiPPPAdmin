<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\TokenHelper;
use App\Traits\ValidatorTrait;
use App\Traits\RolePermissions;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException as ExceptionsJWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTAuth as JWTAuthJWTAuth;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


/**
 * @OA\Info(
 *     title="Autenticación",
 *     version="1.0",
 *     description="Documentación para gestionar autenticaciones"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000"
 * )
 */

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID del usuario"),
 *     @OA\Property(property="username", type="string", description="Nombre del usuario"),
 *     @OA\Property(property="email", type="string", description="Correo electrónico del usuario"),
 *     @OA\Property(property="imagen_url", type="string", description="URL de la imagen del usuario")
 * )
 */
class AuthController
{
    use RolePermissions, ApiResponseTrait, TokenHelper, ValidatorTrait, HasRoles;

    /**
     * @OA\Post(
     *     path="/register",
     *     operationId="registerUserssssssssssssssssss",
     *     tags={"Auth"},
     *     summary="Registrar un nuevo usuario",
     *     description="Registrar un nuevo usuario en el sistema",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="juanito"),
     *             @OA\Property(property="email", type="string", example="juanito@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado correctamente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos inválidos"
     *     )
     * )
     */
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
        $this->assignRoleToUser($user, 'usuario');

        // Crear token JWT
        $token = FacadesJWTAuth::fromUser($user);

        return $this->successResponse([
            'token' => $token,
            'user'  => $user->only(['id', 'username', 'email']),
            'roles' => $user->getRoleNames(),
        ], 'Usuario registrado correctamente', 201);
    }


    /**
     * @OA\Post(
     *     path="/login",
     *     operationId="login",
     *     tags={"Auth"},
     *     summary="Iniciar sesión",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="email", type="string", example="juanito@example.com"),
     *             @OA\Property(property="username", type="string", example="juanito"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario iniciado sesión correctamente"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)
            ->orWhere('username', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Credenciales incorrectas', 401);
        }

        // Obtén el token actual
        $token = FacadesJWTAuth::getToken();

        // Verificar si el token se obtiene correctamente
        if ($token) {
            // Invalidar el token
            FacadesJWTAuth::invalidate($token);
            Log::info('Token invalidado');
        }


        // Intentamos autenticar al usuario con las credenciales proporcionadas
        if (! $user = auth('api')->setTTL(config('jwt.ttl'))->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        // Obtener al usuario autenticado
        $user = auth('api')->user();

        $token = JWTAuth::fromUser($user); // Usar el método fromUser() para agregar los claims


        return $this->successResponse([
            'token' => $token,
            'expires_at' => now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'username' => $user->only(['id', 'name', 'last_name', 'username', 'email']),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 'Usuario iniciado sesión correctamente', 200);
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
            if (!$user = FacadesJWTAuth::setToken($token)->authenticate()) {
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


    /**
     * @OA\Get(
     *     path="/perfil",
     *     operationId="perfil",
     *     summary="Obtener perfil del usuario autenticado",
     *     tags={"Auth"},
     *     security={{"passport":{}}},  // Puedes modificar esto a "jwt" si es necesario
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario autenticado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function perfil(Request $request)
    {
        $user = $request->user();
        return $this->successResponse([
            'username' => $user->username,
            'email' => $user->email,
        ], 'Datos del usuario obtenidos correctamente');
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     operationId="logout",
     *     summary="Cerrar sesión",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            // Revocar el token del usuario
            FacadesJWTAuth::invalidate(FacadesJWTAuth::getToken());
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
            $user = FacadesJWTAuth::parseToken()->authenticate();

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
                    $newToken = FacadesJWTAuth::fromUser($user);
                } catch (FacadesJWTAuth $e) {
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
