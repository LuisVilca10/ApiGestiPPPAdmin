<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class UserController
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $search       = $request->input('search');

        $query = User::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($size, ['*'], 'page', $frontendPage + 1);

        return $this->successResponse([
            'content'       => UserResource::collection($data->items()),
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function show($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return $this->errorResponse('Usuario no encontrado', 404);
        }

        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
        $data = [
            'name'      => $request->name,
            'last_name' => $request->last_name,
            'code'      => $request->code,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ];

        if ($request->filled('academic_cycle')) {
            $data['academic_cycle'] = $request->academic_cycle;
        }

        $user = User::create($data);

        $user->assignRole($request->filled('role') ? $request->role : 'Estudiante');

        return (new UserResource($user->load('roles')))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Usuario no encontrado', 404);
        }

        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return new UserResource($user->load('roles'));
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Usuario no encontrado', 404);
        }

        $user->delete();

        return $this->successResponse([], 'Usuario eliminado correctamente');
    }
}
