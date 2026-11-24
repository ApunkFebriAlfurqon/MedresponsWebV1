<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Token JWT tidak ditemukan.'], 401);
        }

        try {
            $payload = JWT::decode($token, new Key($this->jwtSecret(), 'HS256'));
            $user = User::query()->find($payload->sub ?? null);

            if (! $user) {
                return response()->json(['message' => 'User tidak ditemukan.'], 401);
            }

            $request->setUserResolver(fn () => $user);
        } catch (\Throwable) {
            return response()->json(['message' => 'Token JWT tidak valid atau sudah kedaluwarsa.'], 401);
        }

        return $next($request);
    }

    private function jwtSecret(): string
    {
        $secret = env('JWT_SECRET');

        return is_string($secret) && $secret !== '' ? $secret : (string) config('app.key');
    }
}
