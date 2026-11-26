<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    private const PHONE_RULE = 'regex:/^01[3-9]\d{8}$/';

    public function sendOtp(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'phone' => ['required', self::PHONE_RULE],
            'purpose' => 'required|in:register,login',
            'name' => 'nullable|string|max:100',
        ]);

        $exists = User::where('phone', $data['phone'])->where('is_admin', false)->exists();

        abort_if($data['purpose'] === 'register' && $exists, 422, 'This number is already registered. Please login.');
        abort_if($data['purpose'] === 'login' && ! $exists, 422, 'No account found for this number. Please register.');

        if (! empty($data['name'])) {
            Cache::put('reg_name_'.$data['phone'], $data['name'], now()->addMinutes(10));
        }

        $existingUser = User::where('phone', $data['phone'])->first();

        $code = $otp->send(
            $data['phone'],
            $request->input('onesignal_id') ?? $request->input('player_id'),
            $existingUser?->id
        );

        $response = [
            'message' => 'OTP sent to your phone.',
            'expires_in' => OtpService::EXPIRES_IN,
            'delivery_method' => 'notification',
        ];

        if (app()->environment('local', 'testing')) {
            $response['debug_otp'] = $code;
        }

        return response()->json($response);
    }

    public function resendOtp(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'phone' => ['required', self::PHONE_RULE],
            'purpose' => 'nullable|in:register,login',
        ]);

        $exists = User::where('phone', $data['phone'])->where('is_admin', false)->exists();

        if (isset($data['purpose'])) {
            abort_if($data['purpose'] === 'register' && $exists, 422, 'This number is already registered. Please login.');
            abort_if($data['purpose'] === 'login' && ! $exists, 422, 'No account found for this number. Please register.');
        }

        $existingUser = User::where('phone', $data['phone'])->first();

        $code = $otp->send(
            $data['phone'],
            $request->input('onesignal_id') ?? $request->input('player_id'),
            $existingUser?->id
        );

        $response = [
            'message' => 'OTP resent to your phone.',
            'expires_in' => OtpService::EXPIRES_IN,
            'delivery_method' => 'notification',
        ];

        if (app()->environment('local', 'testing')) {
            $response['debug_otp'] = $code;
        }

        return response()->json($response);
    }

    public function register(Request $request, OtpService $otp)
    {
        // Step 1: User provides name and phone -> sends OTP
        if (! $request->has('otp')) {
            $data = $request->validate([
                'name' => 'required|string|max:100',
                'phone' => ['required', self::PHONE_RULE, 'unique:users,phone'],
            ]);

            Cache::put('reg_name_'.$data['phone'], $data['name'], now()->addMinutes(10));
            $otp->send($data['phone']);

            return response()->json([
                'message' => 'OTP sent to your phone.',
                'expires_in' => OtpService::EXPIRES_IN,
            ]);
        }

        // Step 2: User provides OTP to complete registration and get token
        $data = $request->validate([
            'phone' => ['required', self::PHONE_RULE, 'unique:users,phone'],
            'otp' => 'required|digits:6',
            'name' => 'nullable|string|max:100',
        ]);

        $name = $data['name'] ?? Cache::get('reg_name_'.$data['phone']);
        abort_if(! $name, 422, 'The name field is required.');

        $otp->verify($data['phone'], $data['otp']);

        Cache::forget('reg_name_'.$data['phone']);

        $user = User::create(['name' => $name, 'phone' => $data['phone']]);

        return response()->json([
            'token' => $user->createToken('app')->plainTextToken,
            'user' => new UserResource($user),
        ], 201);
    }

    public function verifyOtp(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'phone' => ['required', self::PHONE_RULE],
            'otp' => 'required|digits:6',
            'name' => 'nullable|string|max:100',
        ]);

        $user = User::where('phone', $data['phone'])->where('is_admin', false)->first();

        // If account already exists -> login flow
        if ($user) {
            abort_if($user->is_blocked, 403, 'Your account has been blocked. Contact support.');
            $otp->verify($data['phone'], $data['otp']);

            return response()->json([
                'token' => $user->createToken('app')->plainTextToken,
                'user' => new UserResource($user),
            ]);
        }

        // New account -> registration completion flow
        $name = $data['name'] ?? Cache::get('reg_name_'.$data['phone']);
        abort_if(! $name, 422, 'The name field is required.');

        $otp->verify($data['phone'], $data['otp']);

        Cache::forget('reg_name_'.$data['phone']);

        $user = User::create(['name' => $name, 'phone' => $data['phone']]);

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
