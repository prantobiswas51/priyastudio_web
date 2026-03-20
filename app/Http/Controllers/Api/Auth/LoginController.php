<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginApiRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function __invoke(LoginApiRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        $user = User::query()
            ->where('email', $request->string('email')->toString())
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), (string) $user->password)) {
            RateLimiter::hit($request->throttleKey());

            return response()->json([
                'message' => trans('auth.failed'),
            ], 422);
        }

        RateLimiter::clear($request->throttleKey());

        $token = $user->createToken($request->string('device_name')->toString())->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $user,
        ]);
    }
}
