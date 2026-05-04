<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * POST /auth/login
     * Authenticate employee and return JWT tokens.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $employeeId = $request->input('employee_id');

        // Rate limiting: max 5 attempts per 15 minutes per employee ID
        $rateLimitKey = 'login:' . $employeeId;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'message' => "Too many login attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $employee = Employee::where('employee_id', $employeeId)->first();

        if (!$employee || !Hash::check($request->input('password'), $employee->password)) {
            RateLimiter::hit($rateLimitKey, 900); // 15 minutes
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (!$employee->is_active) {
            return response()->json([
                'message' => 'Account is deactivated. Contact your administrator.',
            ], 403);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($rateLimitKey);

        // Generate JWT access token (uses default TTL from config = 480min = 8h)
        $accessToken = JWTAuth::fromUser($employee);

        // Generate refresh token with custom claim (uses same TTL, refresh window handles expiry)
        $refreshToken = JWTAuth::claims(['refresh' => true])->fromUser($employee);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'employee' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $employee->role,
                'enrollment_status' => $employee->enrollment_status,
                'is_active' => $employee->is_active,
            ],
        ]);
    }

    /**
     * POST /auth/refresh
     * Exchange refresh token for a new access token.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Could not refresh token.',
            ], 401);
        }
    }

    /**
     * POST /auth/logout
     * Invalidate the current JWT token.
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Token may already be invalid, that's fine
        }

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * GET /employees/me
     * Return the authenticated employee's profile.
     */
    public function me(): JsonResponse
    {
        $employee = auth()->user();

        return response()->json([
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => $employee->role,
            'enrollment_status' => $employee->enrollment_status,
            'is_active' => $employee->is_active,
        ]);
    }
}
