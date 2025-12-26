<?php

namespace App\Services;

use App\Models\OtpVerification;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.sms_tracker.api_key', '');
        $this->apiUrl = config('services.sms_tracker.api_url', 'https://www.traccar.org/sms/');
    }
    /**
     * Generate a 6-digit OTP code.
     *
     * @return string
     */
    protected function generateOtpCode(): string
    {
        return str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP to mobile number.
     *
     * @param string $mobileNumber
     * @return array
     */
    public function sendOtp(string $mobileNumber): array
    {
        // Invalidate any existing unverified OTPs for this mobile number
        OtpVerification::where('mobile_number', $mobileNumber)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->update(['expires_at' => Carbon::now()->subMinute()]);

        // Generate new OTP
        $otpCode = $this->generateOtpCode();
        $expiresAt = Carbon::now()->addMinutes(5); // OTP expires in 5 minutes

        // Store OTP in database
        $otpVerification = OtpVerification::create([
            'mobile_number' => $mobileNumber,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Send SMS via Tracker
        $smsResult = $this->sendSms($mobileNumber, $otpCode);
        
        // Log OTP generation
        Log::info('OTP generated', [
            'mobile_number' => $mobileNumber,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'sms_sent' => $smsResult['success'],
        ]);

        $response = [
            'success' => true,
            'message' => 'OTP sent successfully',
            'otp_id' => $otpVerification->id,
        ];

        // Include OTP code only in development/testing (when API key is not configured)
        if (empty($this->apiKey) || app()->environment('local', 'testing')) {
            $response['otp_code'] = $otpCode;
        }

        return $response;
    }

    /**
     * Verify OTP code.
     *
     * @param string $mobileNumber
     * @param string $otpCode
     * @return array
     */
    public function verifyOtp(string $mobileNumber, string $otpCode): array
    {
        $otpVerification = OtpVerification::where('mobile_number', $mobileNumber)
            ->where('otp_code', $otpCode)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpVerification) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP code',
            ];
        }

        // Mark OTP as verified
        $otpVerification->markAsVerified();

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'otp_id' => $otpVerification->id,
        ];
    }

    /**
     * Check if mobile number has a verified OTP.
     *
     * @param string $mobileNumber
     * @return bool
     */
    public function isOtpVerified(string $mobileNumber): bool
    {
        return OtpVerification::where('mobile_number', $mobileNumber)
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Send SMS via Tracker SMS service.
     *
     * @param string $mobileNumber
     * @param string $otpCode
     * @param string $message
     * @return array
     */
    protected function sendSms(string $mobileNumber, string $otpCode, string $message = 'Your OTP code is'): array
    {
        // Check if API key is configured
        if (empty($this->apiKey)) {
            Log::warning('SMS Tracker API key not configured. SMS not sent.', [
                'mobile_number' => $mobileNumber,
                'otp_code' => $otpCode,
            ]);
            
            return [
                'success' => false,
                'message' => 'SMS service not configured',
            ];
        }

        $requestBody = [
            'message' => sprintf('%s %s', $message, $otpCode),
            'to' => $mobileNumber,
        ];

        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $requestBody,
            ]);

            $responseBody = $response->getBody()->getContents();
            
            Log::info('SMS sent successfully', [
                'mobile_number' => $mobileNumber,
                'response' => $responseBody,
            ]);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'response' => $responseBody,
            ];
        } catch (RequestException $e) {
            Log::error('Failed to send SMS', [
                'mobile_number' => $mobileNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
            ];
        }
    }
}

