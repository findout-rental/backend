<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends Controller
{
    /**
     * List all active apartments available for booking.
     * Includes filtering, searching, and sorting capabilities.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Apartment::where('status', 'active')
            ->with(['owner', 'ratings']);

        // Filter by governorate
        if ($request->has('governorate') && !empty($request->governorate)) {
            $query->where(function ($q) use ($request) {
                $q->where('governorate', 'like', '%' . $request->governorate . '%')
                    ->orWhere('governorate_ar', 'like', '%' . $request->governorate . '%');
            });
        }

        // Filter by city
        if ($request->has('city') && !empty($request->city)) {
            $query->where(function ($q) use ($request) {
                $q->where('city', 'like', '%' . $request->city . '%')
                    ->orWhere('city_ar', 'like', '%' . $request->city . '%');
            });
        }

        // Filter by price range (nightly)
        if ($request->has('min_nightly_price')) {
            $query->where('nightly_price', '>=', $request->min_nightly_price);
        }
        if ($request->has('max_nightly_price')) {
            $query->where('nightly_price', '<=', $request->max_nightly_price);
        }

        // Filter by price range (monthly)
        if ($request->has('min_monthly_price')) {
            $query->where('monthly_price', '>=', $request->min_monthly_price);
        }
        if ($request->has('max_monthly_price')) {
            $query->where('monthly_price', '<=', $request->max_monthly_price);
        }

        // Filter by bedrooms
        if ($request->has('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }
        if ($request->has('min_bedrooms')) {
            $query->where('bedrooms', '>=', $request->min_bedrooms);
        }

        // Filter by bathrooms
        if ($request->has('bathrooms')) {
            $query->where('bathrooms', $request->bathrooms);
        }
        if ($request->has('min_bathrooms')) {
            $query->where('bathrooms', '>=', $request->min_bathrooms);
        }

        // Filter by amenities
        if ($request->has('amenities') && is_array($request->amenities)) {
            foreach ($request->amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        // Search by address, city, or governorate
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('address', 'like', $searchTerm)
                    ->orWhere('address_ar', 'like', $searchTerm)
                    ->orWhere('governorate', 'like', $searchTerm)
                    ->orWhere('governorate_ar', 'like', $searchTerm)
                    ->orWhere('city', 'like', $searchTerm)
                    ->orWhere('city_ar', 'like', $searchTerm);
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Handle special sorting cases
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('nightly_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('nightly_price', 'desc');
                break;
            case 'rating':
                // Sort by average rating (requires subquery or join)
                $query->withAvg('ratings', 'rating')
                    ->orderBy('ratings_avg_rating', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $apartments = $query->paginate($perPage);

        // Format apartments for response
        $formattedApartments = $apartments->map(function ($apartment) {
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
                'owner' => [
                    'id' => $apartment->owner->id,
                    'first_name' => $apartment->owner->first_name,
                    'last_name' => $apartment->owner->last_name,
                    'personal_photo' => $apartment->owner->personal_photo ? Storage::url($apartment->owner->personal_photo) : null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Apartments retrieved successfully',
            'data' => [
                'apartments' => $formattedApartments,
                'pagination' => [
                    'current_page' => $apartments->currentPage(),
                    'last_page' => $apartments->lastPage(),
                    'per_page' => $apartments->perPage(),
                    'total' => $apartments->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Get detailed information about a specific apartment.
     *
     * @param int $apartment_id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $apartment_id, Request $request): JsonResponse
    {
        $apartment = Apartment::where('id', $apartment_id)
            ->where('status', 'active')
            ->with(['owner', 'ratings'])
            ->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found or not available',
            ], 404);
        }

        $photos = $apartment->photos ?? [];
        $photoUrls = array_map(function ($photo) {
            return Storage::url($photo);
        }, $photos);

        // Get recent ratings/reviews (limit to 10 most recent)
        $recentRatings = $apartment->ratings()
            ->with('tenant:id,first_name,last_name,personal_photo')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'comment' => $rating->review_text,
                    'user' => [
                        'id' => $rating->tenant->id,
                        'first_name' => $rating->tenant->first_name,
                        'last_name' => $rating->tenant->last_name,
                        'personal_photo' => $rating->tenant->personal_photo ? Storage::url($rating->tenant->personal_photo) : null,
                    ],
                    'created_at' => $rating->created_at->toISOString(),
                ];
            });

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
                    'nightly_price' => number_format($apartment->nightly_price, 2, '.', ''),
                    'monthly_price' => number_format($apartment->monthly_price, 2, '.', ''),
                    'bedrooms' => $apartment->bedrooms,
                    'bathrooms' => $apartment->bathrooms,
                    'living_rooms' => $apartment->living_rooms,
                    'size' => number_format($apartment->size, 2, '.', ''),
                    'description' => $apartment->description,
                    'description_ar' => $apartment->description_ar,
                    'amenities' => $apartment->amenities ?? [],
                    'average_rating' => round($apartment->ratings()->avg('rating') ?? 0, 1),
                    'rating_count' => $apartment->ratings()->count(),
                    'owner' => [
                        'id' => $apartment->owner->id,
                        'first_name' => $apartment->owner->first_name,
                        'last_name' => $apartment->owner->last_name,
                        'personal_photo' => $apartment->owner->personal_photo ? Storage::url($apartment->owner->personal_photo) : null,
                        'average_rating' => round($apartment->owner->apartments()
                            ->with('ratings')
                            ->get()
                            ->flatMap->ratings
                            ->avg('rating') ?? 0, 1),
                    ],
                    'reviews' => $recentRatings,
                    'created_at' => $apartment->created_at->toISOString(),
                    'updated_at' => $apartment->updated_at->toISOString(),
                ],
            ],
        ], 200);
    }
}
