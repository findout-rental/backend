<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Normalize Syrian mobile number to +963 format.
     *
     * @param string $mobileNumber
     * @return string
     */
    protected function normalizeMobileNumber(string $mobileNumber): string
    {
        // If it starts with 0, replace with +963
        if (str_starts_with($mobileNumber, '0')) {
            return '+963' . substr($mobileNumber, 1);
        }
        
        // If it already starts with +963, return as is
        if (str_starts_with($mobileNumber, '+963')) {
            return $mobileNumber;
        }
        
        // Should not reach here if validation is correct, but return as is
        return $mobileNumber;
    }

    /**
     * Send OTP to mobile number for registration.
     *
     * @param SendOtpRequest $request
     * @return JsonResponse
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $normalizedMobile = $this->normalizeMobileNumber($request->mobile_number);
        $result = $this->otpService->sendOtp($normalizedMobile);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'otp_id' => $result['otp_id'],
                    // Remove otp_code in production
                    'otp_code' => $result['otp_code'] ?? null, // Only for development/testing
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Verify OTP code.
     *
     * @param VerifyOtpRequest $request
     * @return JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $normalizedMobile = $this->normalizeMobileNumber($request->mobile_number);
        $result = $this->otpService->verifyOtp($normalizedMobile, $request->otp_code);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'otp_id' => $result['otp_id'],
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Normalize mobile number
        $normalizedMobile = $this->normalizeMobileNumber($request->mobile_number);
        
        // Verify OTP first
        $otpResult = $this->otpService->verifyOtp($normalizedMobile, $request->otp_code);

        if (!$otpResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code',
            ], 400);
        }

        // Check if OTP was already used for registration
        $existingUser = User::where('mobile_number', $normalizedMobile)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This mobile number is already registered',
            ], 400);
        }

        // Handle file uploads
        $personalPhotoPath = $request->file('personal_photo')->store('users/photos', 'public');
        $idPhotoPath = $request->file('id_photo')->store('users/id-photos', 'public');

        // Create user
        $user = User::create([
            'mobile_number' => $normalizedMobile,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'personal_photo' => $personalPhotoPath,
            'date_of_birth' => $request->date_of_birth,
            'id_photo' => $idPhotoPath,
            'role' => $request->role,
            'status' => 'pending', // User must be approved by admin
            'language_preference' => 'en',
            'balance' => 0.00,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please wait for admin approval.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
            ],
        ], 201);
    }

    /**
     * Login user and return JWT token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $normalizedMobile = $this->normalizeMobileNumber($request->mobile_number);
        $user = User::where('mobile_number', $normalizedMobile)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number or password',
            ], 401);
        }

        // Check if user is approved
        if ($user->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending approval. Please wait for admin approval.',
            ], 403);
        }

        // Generate JWT token with role in claims
        $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60, // in seconds
                'user' => [
                    'id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'personal_photo' => Storage::url($user->personal_photo),
                    'role' => $user->role,
                    'status' => $user->status,
                    'language_preference' => $user->language_preference,
                    'balance' => $user->balance,
                ],
            ],
        ], 200);
    }

    /**
     * Logout user (invalidate token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
            ], 500);
        }
    }

    /**
     * Get authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'personal_photo' => Storage::url($user->personal_photo),
                    'date_of_birth' => $user->date_of_birth,
                    'role' => $user->role,
                    'status' => $user->status,
                    'language_preference' => $user->language_preference,
                    'balance' => $user->balance,
                ],
            ],
        ], 200);
    }
}
