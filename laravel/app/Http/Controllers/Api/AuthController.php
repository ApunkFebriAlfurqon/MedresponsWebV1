<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        $now = time();
        $token = JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'exp' => $now + 3600,
        ], $this->jwtSecret(), 'HS256');

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Berhasil logout.']);
    }

    private function jwtSecret(): string
    {
        $secret = env('JWT_SECRET');

        return is_string($secret) && $secret !== '' ? $secret : (string) config('app.key');
    }
}
