<?php

namespace App\Http\Middleware;

use App\Models\OtpVerification;
use App\Services\OtpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpIsVerified
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Handle an incoming request.
     * Checks if the mobile number has a verified OTP.
     * For authenticated requests, checks the authenticated user's mobile number.
     * For unauthenticated requests, checks the mobile_number from request body.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mobileNumber = null;

        // If user is authenticated, use their mobile number
        if ($request->user()) {
            $mobileNumber = $request->user()->mobile_number;
        } 
        // Otherwise, get mobile number from request
        elseif ($request->has('mobile_number')) {
            $mobileNumber = $request->input('mobile_number');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number is required',
            ], 400);
        }

        // Normalize mobile number
        $normalizedMobile = $this->normalizeMobileNumber($mobileNumber);

        // Check if OTP is verified (for authenticated users, check if they have any verified OTP record)
        // For authenticated users, we check if they have ever verified an OTP (not just current)
        if ($request->user()) {
            // For authenticated users, check if they have a verified OTP record
            $hasVerifiedOtp = OtpVerification::where('mobile_number', $normalizedMobile)
                ->whereNotNull('verified_at')
                ->exists();
            
            if (!$hasVerifiedOtp) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP verification required. Please verify your OTP first.',
                ], 403);
            }
        } else {
            // For unauthenticated requests, check if there's a current verified OTP
            if (!$this->otpService->isOtpVerified($normalizedMobile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP verification required. Please verify your OTP first.',
                ], 403);
            }
        }

        return $next($request);
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
}
