<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|max:255|unique:users',
            'last_name' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'username'  => 'required|string|max:255|unique:users',
            'rol'       => 'required|in:1,2',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'rol' => $request->rol,
            'password' => bcrypt($request->password),
        ]);

        // if (request()->rol == '1') {
        //     $rolNombre = 'usuario';
        // } elseif (request()->rol == '2') {
        //     $rolNombre = 'admin_familia';
        // }
        // $this->assignRoleToUser($user, $rolNombre);

        if (!$token = auth('api')->login($user)) {
            return response()->json(['error' => 'No se pudo iniciar sesión'], 401);
        }

        return $this->respondWithToken($token);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        // Intentamos autenticar
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
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
                // 'roles' => $user->getRoleNames(),
                // 'permissions' => $user->getAllPermissions()->pluck('name'),
            ], 'Sesión activa', 200);
        } catch (TokenExpiredException $e) {
            return $this->error('Token expirado, inicia sesión de nuevo', 401);
        } catch (TokenInvalidException $e) {
            return $this->error('Token inválido', 401);
        } catch (\Exception $e) {
            // Cualquier otro error
            return $this->error('No autorizado', 401);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->successResponse(null, 'Sesión cerrada');
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        try {
            return response()->json([
                'token' => $token,
                "status" => true,
                'token_type' => 'bearer',
                'message' => 'Usuario iniciado sesión correctamente',
                'user' => auth('api')->user()->only(['id', 'name', 'last_name', 'username', 'email']),
                'expires_in' => auth('api')->factory()->getTTL() * 60, // segundos
                'expires_at' => now()->addMinutes(config('jwt.ttl'))->toDateTimeString() // fecha exacta
                // 'roles' => $user->getRoleNames(),
                // 'permissions' => $user->getAllPermissions()->pluck('name'),
            ], 200);
        } catch (TokenExpiredException $e) {
            return $this->error('Token expirado, inicia sesión de nuevo', 401);
        } catch (TokenInvalidException $e) {
            return $this->error('Token inválido', 401);
        } catch (\Exception $e) {
            // Cualquier otro error
            return $this->error('No autorizado', 401);
        }
    }
}
