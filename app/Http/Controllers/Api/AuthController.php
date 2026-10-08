<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private const PHONE_RULE = 'regex:/^01[3-9]\d{8}$/';

    public function sendOtp(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'phone' => ['required', self::PHONE_RULE],
            'purpose' => 'required|in:register,login',
        ]);

        $exists = User::where('phone', $data['phone'])->where('is_admin', false)->exists();

        abort_if($data['purpose'] === 'register' && $exists, 422, 'This number is already registered. Please login.');
        abort_if($data['purpose'] === 'login' && ! $exists, 422, 'No account found for this number. Please register.');

        $otp->send($data['phone']);

        return response()->json([
            'message' => 'OTP sent to your phone.',
            'expires_in' => OtpService::EXPIRES_IN,
        ]);
    }

    public function register(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', self::PHONE_RULE, 'unique:users,phone'],
            'otp' => 'required|digits:6',
        ]);

        $otp->verify($data['phone'], $data['otp']);

        $user = User::create(['name' => $data['name'], 'phone' => $data['phone']]);

        return response()->json([
            'token' => $user->createToken('app')->plainTextToken,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'phone' => ['required', self::PHONE_RULE],
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('phone', $data['phone'])->where('is_admin', false)->first();

        abort_if(! $user, 422, 'No account found for this number. Please register.');

        $otp->verify($data['phone'], $data['otp']);

        abort_if($user->is_blocked, 403, 'Your account has been blocked. Contact support.');

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
