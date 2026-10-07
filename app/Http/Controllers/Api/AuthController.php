<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create($data);

        return response()->json([
            'token' => $user->createToken('app')->plainTextToken,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $data['phone'])->where('is_admin', false)->first();

        if (! $user || ! password_verify($data['password'], $user->password)) {
            throw ValidationException::withMessages(['phone' => 'Phone or password is incorrect.']);
        }

        if ($user->is_blocked) {
            return response()->json(['message' => 'Your account has been blocked. Contact support.'], 403);
        }

        return response()->json([
            'token' => $user->createToken('app')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
