<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_va_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:khach_hangs',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = KhachHang::create([
            'ho_va_ten' => $request->ho_va_ten,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->id; // Sử dụng user ID làm token

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        $user = KhachHang::where('email', $request->email)->firstOrFail();
        $token = $user->id; // Sử dụng user ID làm token

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Đơn giản hóa - không cần xóa token
        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user
        ]);
    }
}
