<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\KhachHang;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token không được cung cấp'
            ], 401);
        }

        // Tìm user theo token (đơn giản hóa)
        $user = KhachHang::where('id', $token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ'
            ], 401);
        }

        $request->merge(['user' => $user]);

        return $next($request);
    }
}
