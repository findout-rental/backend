<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends Controller
{
    /**
     * List all apartments owned by the authenticated owner.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $apartments = Apartment::where('owner_id', $user->id)
            ->with(['bookings', 'ratings'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $totalApartments = $apartments->count();
        $activeApartments = $apartments->where('status', 'active')->count();
        $pendingBookings = Booking::whereIn('apartment_id', $apartments->pluck('id'))
            ->where('status', 'pending')
            ->count();

        // Format apartments with additional data
        $formattedApartments = $apartments->map(function ($apartment) {
            $photos = $apartment->photos ?? [];
            $photoUrls = array_map(function ($photo) {
                return Storage::url($photo);
            }, $photos);

            return [
                'id' => $apartment->id,
                'photos' => $photoUrls,
                'address' => $apartment->address,
                'governorate' => $apartment->governorate,
                'city' => $apartment->city,
                'nightly_price' => $apartment->nightly_price,
                'monthly_price' => $apartment->monthly_price,
                'status' => $apartment->status,
                'bedrooms' => $apartment->bedrooms,
                'bathrooms' => $apartment->bathrooms,
                'living_rooms' => $apartment->living_rooms,
                'size' => $apartment->size,
                'average_rating' => round($apartment->ratings()->avg('rating') ?? 0, 1),
                'rating_count' => $apartment->ratings()->count(),
                'pending_requests_count' => $apartment->bookings()->where('status', 'pending')->count(),
                'total_bookings_count' => $apartment->bookings()->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_apartments' => $totalApartments,
                    'active_apartments' => $activeApartments,
                    'pending_bookings' => $pendingBookings,
                ],
                'apartments' => $formattedApartments,
            ],
        ], 200);
    }

    /**
     * Store a newly created apartment.
     *
     * @param StoreApartmentRequest $request
     * @return JsonResponse
     */
    public function store(StoreApartmentRequest $request): JsonResponse
    {
        $user = $request->user();

        // Create apartment
        $apartment = Apartment::create([
            'owner_id' => $user->id,
            'governorate' => $request->governorate,
            'governorate_ar' => $request->governorate_ar,
            'city' => $request->city,
            'city_ar' => $request->city_ar,
            'address' => $request->address,
            'address_ar' => $request->address_ar,
            'nightly_price' => $request->nightly_price,
            'monthly_price' => $request->monthly_price,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'living_rooms' => $request->living_rooms,
            'size' => $request->size,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'photos' => $request->photos,
            'amenities' => $request->amenities ?? [],
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Apartment published successfully',
            'data' => [
                'apartment_id' => $apartment->id,
            ],
        ], 201);
    }

    /**
     * Display the specified apartment.
     *
     * @param int $apartment_id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $apartment_id, Request $request): JsonResponse
    {
        $user = $request->user();

        $apartment = Apartment::where('id', $apartment_id)
            ->where('owner_id', $user->id)
            ->with(['bookings', 'ratings'])
            ->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found',
            ], 404);
        }

        $photos = $apartment->photos ?? [];
        $photoUrls = array_map(function ($photo) {
            return Storage::url($photo);
        }, $photos);

        return response()->json([
            'success' => true,
            'data' => [
                'apartment' => [
                    'id' => $apartment->id,
                    'photos' => $photoUrls,
                    'governorate' => $apartment->governorate,
                    'governorate_ar' => $apartment->governorate_ar,
                    'city' => $apartment->city,
                    'city_ar' => $apartment->city_ar,
                    'address' => $apartment->address,
                    'address_ar' => $apartment->address_ar,
                    'nightly_price' => $apartment->nightly_price,
                    'monthly_price' => $apartment->monthly_price,
                    'bedrooms' => $apartment->bedrooms,
                    'bathrooms' => $apartment->bathrooms,
                    'living_rooms' => $apartment->living_rooms,
                    'size' => $apartment->size,
                    'description' => $apartment->description,
                    'description_ar' => $apartment->description_ar,
                    'amenities' => $apartment->amenities ?? [],
                    'status' => $apartment->status,
                    'average_rating' => round($apartment->ratings()->avg('rating') ?? 0, 1),
                    'rating_count' => $apartment->ratings()->count(),
                    'total_bookings' => $apartment->bookings()->count(),
                    'pending_bookings' => $apartment->bookings()->where('status', 'pending')->count(),
                    'created_at' => $apartment->created_at->toISOString(),
                    'updated_at' => $apartment->updated_at->toISOString(),
                ],
            ],
        ], 200);
    }

    /**
     * Update the specified apartment.
     *
     * @param UpdateApartmentRequest $request
     * @param int $apartment_id
     * @return JsonResponse
     */
    public function update(UpdateApartmentRequest $request, int $apartment_id): JsonResponse
    {
        $user = $request->user();

        $apartment = Apartment::where('id', $apartment_id)
            ->where('owner_id', $user->id)
            ->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found',
            ], 404);
        }

        // Check if trying to set inactive with active/pending bookings
        if ($request->has('status') && $request->status === 'inactive') {
            $hasActiveBookings = $apartment->bookings()
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($hasActiveBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set apartment to inactive. There are active or pending bookings.',
                ], 400);
            }
        }

        // Update apartment
        $updateData = [];
        $fields = [
            'governorate', 'governorate_ar', 'city', 'city_ar',
            'address', 'address_ar', 'nightly_price', 'monthly_price',
            'bedrooms', 'bathrooms', 'living_rooms', 'size',
            'description', 'description_ar', 'amenities', 'status',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        if ($request->has('photos')) {
            // Delete old photos
            $oldPhotos = $apartment->photos ?? [];
            foreach ($oldPhotos as $oldPhoto) {
                if (Storage::disk('public')->exists($oldPhoto)) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }
            $updateData['photos'] = $request->photos;
        }

        $apartment->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Apartment updated successfully',
            'data' => [
                'apartment_id' => $apartment->id,
            ],
        ], 200);
    }

    /**
     * Remove the specified apartment.
     *
     * @param int $apartment_id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $apartment_id, Request $request): JsonResponse
    {
        $user = $request->user();

        $apartment = Apartment::where('id', $apartment_id)
            ->where('owner_id', $user->id)
            ->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found',
            ], 404);
        }

        // Check for active or pending bookings
        $hasActiveBookings = $apartment->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasActiveBookings) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete apartment. There are active or pending bookings.',
            ], 400);
        }

        // Delete photos
        $photos = $apartment->photos ?? [];
        foreach ($photos as $photo) {
            if (Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->delete($photo);
            }
        }

        // Delete apartment
        $apartment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Apartment deleted successfully',
        ], 200);
    }

    /**
     * Upload apartment photo.
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

        $photoPath = $request->file('photo')->store('apartments/photos', 'public');

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully',
            'data' => [
                'file_path' => $photoPath, // Return path (not URL) to be stored in photos array
            ],
        ], 200);
    }
}
