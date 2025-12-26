<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FavoriteController extends Controller
{
    /**
     * List all favorite apartments for the authenticated tenant.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user();
        $perPage = $request->per_page ?? 20;
        $page = $request->page ?? 1;

        $favorites = Favorite::where('tenant_id', $tenant->id)
            ->with(['apartment.ratings'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $apartments = $favorites->map(function ($favorite) {
            $apartment = $favorite->apartment;
            $photos = $apartment->photos ?? [];
            $photoUrls = array_map(function ($photo) {
                return Storage::url($photo);
            }, $photos);

            return [
                'id' => $apartment->id,
                'photos' => $photoUrls,
                'governorate' => $apartment->governorate,
                'governorate_ar' => $apartment->governorate_ar,
                'city' => $apartment->city,
                'city_ar' => $apartment->city_ar,
                'address' => $apartment->address,
                'address_ar' => $apartment->address_ar,
                'nightly_price' => number_format($apartment->nightly_price, 2, '.', ''),
                'monthly_price' => number_format($apartment->monthly_price, 2, '.', ''),
                'bedrooms' => $apartment->bedrooms,
                'bathrooms' => $apartment->bathrooms,
                'living_rooms' => $apartment->living_rooms,
                'size' => number_format($apartment->size, 2, '.', ''),
                'amenities' => $apartment->amenities ?? [],
                'average_rating' => round($apartment->ratings()->avg('rating') ?? 0, 1),
                'rating_count' => $apartment->ratings()->count(),
                'favorited_at' => $favorite->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'apartments' => $apartments,
                'pagination' => [
                    'current_page' => $favorites->currentPage(),
                    'last_page' => $favorites->lastPage(),
                    'per_page' => $favorites->perPage(),
                    'total' => $favorites->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Add an apartment to favorites.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'apartment_id' => ['required', 'integer', 'exists:apartments,id'],
        ]);

        $tenant = $request->user();
        $apartmentId = $request->apartment_id;

        // Check if apartment exists and is active
        $apartment = Apartment::where('id', $apartmentId)
            ->where('status', 'active')
            ->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found or not available',
            ], 404);
        }

        // Check if already favorited
        $existingFavorite = Favorite::where('tenant_id', $tenant->id)
            ->where('apartment_id', $apartmentId)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment is already in your favorites',
            ], 400);
        }

        // Create favorite
        $favorite = Favorite::create([
            'tenant_id' => $tenant->id,
            'apartment_id' => $apartmentId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Apartment added to favorites',
            'data' => [
                'favorite_id' => $favorite->id,
                'apartment_id' => $apartmentId,
                'favorited_at' => $favorite->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * Remove an apartment from favorites.
     *
     * @param int $apartment_id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $apartment_id, Request $request): JsonResponse
    {
        $tenant = $request->user();

        $favorite = Favorite::where('tenant_id', $tenant->id)
            ->where('apartment_id', $apartment_id)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment is not in your favorites',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Apartment removed from favorites',
        ], 200);
    }
}

