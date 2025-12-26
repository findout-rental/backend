<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLanguageRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get authenticated user profile with statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Calculate statistics based on user role
        $statistics = [];

        if ($user->isOwner()) {
            // Owner statistics
            $statistics = [
                'total_apartments' => $user->apartments()->count(),
                'total_bookings' => $user->apartments()->withCount('bookings')->get()->sum('bookings_count'),
                'average_rating' => $user->apartments()
                    ->with('ratings')
                    ->get()
                    ->flatMap->ratings
                    ->avg('rating') ?? 0,
                'reviews_received' => $user->apartments()
                    ->with('ratings')
                    ->get()
                    ->flatMap->ratings
                    ->count(),
            ];
        } elseif ($user->isTenant()) {
            // Tenant statistics
            $statistics = [
                'total_bookings' => $user->bookings()->count(),
                'average_rating' => $user->ratings()->avg('rating') ?? 0,
                'reviews_given' => $user->ratings()->count(),
                'favorites_count' => $user->favorites()->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'personal_photo' => $user->personal_photo ? Storage::url($user->personal_photo) : null,
                    'date_of_birth' => $user->date_of_birth?->toDateString(),
                    'role' => $user->role,
                    'language_preference' => $user->language_preference,
                    'status' => $user->status,
                    'created_at' => $user->created_at->toISOString(),
                ],
                'statistics' => $statistics,
                'balance' => $user->balance,
            ],
        ], 200);
    }

    /**
     * Update user profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $updateData = [];

        if ($request->has('first_name')) {
            $updateData['first_name'] = $request->first_name;
        }

        if ($request->has('last_name')) {
            $updateData['last_name'] = $request->last_name;
        }

        // Handle photo upload if provided
        if ($request->hasFile('personal_photo')) {
            // Delete old photo if exists
            if ($user->personal_photo && Storage::disk('public')->exists($user->personal_photo)) {
                Storage::disk('public')->delete($user->personal_photo);
            }

            // Store new photo
            $updateData['personal_photo'] = $request->file('personal_photo')->store('users/photos', 'public');
        }

        // Update user
        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'personal_photo' => $user->personal_photo ? Storage::url($user->personal_photo) : null,
                ],
            ],
        ], 200);
    }

    /**
     * Upload profile photo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB
            ],
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->personal_photo && Storage::disk('public')->exists($user->personal_photo)) {
            Storage::disk('public')->delete($user->personal_photo);
        }

        // Store new photo
        $photoPath = $request->file('photo')->store('users/photos', 'public');
        $user->update(['personal_photo' => $photoPath]);

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully',
            'data' => [
                'file_path' => Storage::url($photoPath),
                'personal_photo' => Storage::url($photoPath),
            ],
        ], 200);
    }

    /**
     * Update language preference.
     *
     * @param UpdateLanguageRequest $request
     * @return JsonResponse
     */
    public function updateLanguage(UpdateLanguageRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'language_preference' => $request->language_preference,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => [
                'language_preference' => $user->language_preference,
            ],
        ], 200);
    }

    /**
     * View another user's profile (public information only).
     * Available to all authenticated users.
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function show(Request $request, int $userId): JsonResponse
    {
        $viewer = $request->user();
        
        // Find the user to view
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Calculate public statistics based on user role
        $statistics = [];

        if ($user->isOwner()) {
            // Owner statistics (public)
            $statistics = [
                'total_apartments' => $user->apartments()->where('status', 'active')->count(),
                'total_bookings' => $user->apartments()->withCount('bookings')->get()->sum('bookings_count'),
                'average_rating' => $user->apartments()
                    ->with('ratings')
                    ->get()
                    ->flatMap->ratings
                    ->avg('rating') ?? 0,
                'reviews_received' => $user->apartments()
                    ->with('ratings')
                    ->get()
                    ->flatMap->ratings
                    ->count(),
            ];
        } elseif ($user->isTenant()) {
            // Tenant statistics (public)
            $statistics = [
                'total_bookings' => $user->bookings()->whereIn('status', ['approved', 'completed'])->count(),
                'average_rating' => $user->ratings()->avg('rating') ?? 0,
                'reviews_given' => $user->ratings()->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'personal_photo' => $user->personal_photo ? Storage::url($user->personal_photo) : null,
                    'role' => $user->role,
                    'status' => $user->status,
                    'created_at' => $user->created_at->toISOString(),
                ],
                'statistics' => $statistics,
            ],
        ], 200);
    }
}
