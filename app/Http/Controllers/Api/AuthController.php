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

    public function register(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role ?? 'krama', // Allow role setting if needed or default
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

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

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'sometimes|string|max:150',
            'username' => 'sometimes|string|max:50|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', $validator->errors());
        }

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(['message' => 'Logged out successfully']);
    }
}
