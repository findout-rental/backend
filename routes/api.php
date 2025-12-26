<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication + approved status + verified OTP)
Route::middleware(['auth:api', 'approved', 'otp.verified'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // User management routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, 'profile']);
        Route::put('/', [\App\Http\Controllers\UserController::class, 'updateProfile']);
        Route::post('/upload-photo', [\App\Http\Controllers\UserController::class, 'uploadPhoto']);
        Route::put('/language', [\App\Http\Controllers\UserController::class, 'updateLanguage']);
    });

    // View another user's profile (available to all authenticated users)
    Route::get('/users/{userId}', [\App\Http\Controllers\UserController::class, 'show']);

    // Messaging routes (shared between tenants and owners)
    Route::prefix('messages')->group(function () {
        // HTTP endpoints (for initial load and file uploads)
        Route::get('/', [\App\Http\Controllers\MessageController::class, 'index']);
        Route::get('/{user_id}', [\App\Http\Controllers\MessageController::class, 'show']);
        
        // File upload endpoint (HTTP only - easier for multipart/form-data)
        Route::post('/upload-attachment', [\App\Http\Controllers\MessageController::class, 'uploadAttachment']);
        
        // WebSocket message handler (for sending messages, marking as read, typing indicators)
        Route::post('/ws', [\App\Http\Controllers\WebSocketMessageController::class, 'handle']);
    });

    // Owner routes (require authentication + owner role + approved status + verified OTP)
    Route::middleware('owner')->prefix('owner')->group(function () {
        Route::prefix('apartments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\ApartmentController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Owner\ApartmentController::class, 'store']);
            Route::post('/upload-photo', [\App\Http\Controllers\Owner\ApartmentController::class, 'uploadPhoto']);
            Route::get('/{apartment_id}', [\App\Http\Controllers\Owner\ApartmentController::class, 'show']);
            Route::put('/{apartment_id}', [\App\Http\Controllers\Owner\ApartmentController::class, 'update']);
            Route::delete('/{apartment_id}', [\App\Http\Controllers\Owner\ApartmentController::class, 'destroy']);
        });
        
        Route::prefix('bookings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\BookingController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Owner\BookingController::class, 'show']);
            Route::put('/{id}/approve', [\App\Http\Controllers\Owner\BookingController::class, 'approve']);
            Route::put('/{id}/reject', [\App\Http\Controllers\Owner\BookingController::class, 'reject']);
            Route::put('/{id}/approve-modification', [\App\Http\Controllers\Owner\BookingController::class, 'approveModification']);
            Route::put('/{id}/reject-modification', [\App\Http\Controllers\Owner\BookingController::class, 'rejectModification']);
        });
    });

    // Tenant routes (require authentication + tenant role + approved status + verified OTP)
    Route::middleware('tenant')->group(function () {
        Route::prefix('apartments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Tenant\ApartmentController::class, 'index']);
            Route::get('/{apartment_id}', [\App\Http\Controllers\Tenant\ApartmentController::class, 'show']);
        });
        
        Route::prefix('bookings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Tenant\BookingController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Tenant\BookingController::class, 'store']);
            Route::get('/{id}', [\App\Http\Controllers\Tenant\BookingController::class, 'show']);
            Route::put('/{id}', [\App\Http\Controllers\Tenant\BookingController::class, 'update']);
            Route::post('/{id}/cancel', [\App\Http\Controllers\Tenant\BookingController::class, 'cancel']);
        });
        
        Route::prefix('ratings')->group(function () {
            Route::post('/', [\App\Http\Controllers\Tenant\RatingController::class, 'store']);
        });
        
        Route::prefix('favorites')->group(function () {
            Route::get('/', [\App\Http\Controllers\Tenant\FavoriteController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Tenant\FavoriteController::class, 'store']);
            Route::delete('/{apartment_id}', [\App\Http\Controllers\Tenant\FavoriteController::class, 'destroy']);
        });
    });

    // Admin routes (require authentication + admin role + approved status + verified OTP)
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::put('/registrations/{user_id}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve']);
        Route::get('/apartments', [\App\Http\Controllers\Admin\ApartmentController::class, 'index']);
        Route::get('/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index']);
    });
});
