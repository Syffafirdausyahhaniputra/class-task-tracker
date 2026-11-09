<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Registrasi publik hanya boleh membuat akun siswa.
        $data['role'] = 'student';

        // Cast hashed pada model menyimpan password sebagai hash.
        $user = User::create($data);

        return response()->json([
            'data' => [
                'user' => UserResource::make($user)->resolve($request),
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        // Respons yang sama mencegah pembeda antara email dan password yang salah.
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => UserResource::make($user)->resolve($request),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Hanya token yang dipakai untuk request ini yang dicabut.
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'data' => ['message' => 'Logout berhasil.'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => UserResource::make($request->user())->resolve($request),
        ]);
    }
}
