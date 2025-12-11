<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use \App\Traits\ApiResponse;

    public function register(\App\Http\Requests\RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role ?? 'krama',
            'can_create_resident' => true
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(\App\Http\Requests\LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('UNAUTHORIZED', null, 'Invalid login details');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function profile(Request $request)
    {
        return $this->success($request->user());
    }

    public function updateProfile(\App\Http\Requests\UpdateProfileRequest $request)
    {
        $user = $request->user();

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('username')) {
            $user->username = $request->username;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return $this->success([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function changePassword(\App\Http\Requests\ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('VALIDATION_ERROR', ['current_password' => ['The provided password does not match your current password.']], 'Invalid current password', 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->success(['message' => 'Password changed successfully']);
    }

    public function hasResident(Request $request)
    {
        return $this->success([
            'has_resident' => $request->user()->residents()->exists()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(['message' => 'Logged out successfully']);
    }
}
