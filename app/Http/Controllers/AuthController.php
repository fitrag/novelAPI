<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register user baru
    public function register(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            // Buat user baru
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
            ], 201);
        } catch (ValidationException $e) {
            // Tangkap error validasi dan kembalikan pesan error dalam format JSON
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // Login user
    public function login(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Cari user berdasarkan email
            $user = User::where('email', $request->email)->first();

            // Verifikasi password
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Authentication failed',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.'],
                    ],
                ], 401);
            }

            // Generate token untuk autentikasi
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            // Tangkap error lainnya dan kembalikan pesan error dalam format JSON
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Logout user
    public function logout(Request $request)
    {
        try {
            // Hapus token autentikasi saat ini
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            // Tangkap error dan kembalikan pesan error dalam format JSON
            return response()->json([
                'message' => 'An error occurred while logging out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}