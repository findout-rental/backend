<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\JsonResponse;

class ApartmentController extends Controller
{
    /**
     * List all apartments in the system.
     * Supports filtering, searching, and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $statusFilter = $request->query('status');
        $governorateFilter = $request->query('governorate');
        $cityFilter = $request->query('city');
        $search = $request->query('search');
        $sortBy = $request->query('sort', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 25);
        $page = $request->query('page', 1);

        $query = Apartment::with(['owner', 'bookings', 'ratings']);

        // Apply status filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // Apply governorate filter
        if ($governorateFilter) {
            $query->where(function ($q) use ($governorateFilter) {
                $q->where('governorate', 'like', "%{$governorateFilter}%")
                    ->orWhere('governorate_ar', 'like', "%{$governorateFilter}%");
            });
        }

        // Apply city filter
        if ($cityFilter) {
            $query->where(function ($q) use ($cityFilter) {
                $q->where('city', 'like', "%{$cityFilter}%")
                    ->orWhere('city_ar', 'like', "%{$cityFilter}%");
            });
        }

        // Apply search (address, governorate, city)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('address', 'like', "%{$search}%")
                    ->orWhere('address_ar', 'like', "%{$search}%")
                    ->orWhere('governorate', 'like', "%{$search}%")
                    ->orWhere('governorate_ar', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('city_ar', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply sorting
        $allowedSortFields = ['id', 'created_at', 'nightly_price', 'monthly_price', 'status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $apartments = $query->paginate($perPage, ['*'], 'page', $page);

        $apartmentsData = $apartments->map(function ($apartment) {
            $owner = $apartment->owner;
            $totalBookings = $apartment->bookings()->count();
            $averageRating = $apartment->ratings()->avg('rating') ?? 0.0;

            return [
                'id' => $apartment->id,
                'status' => $apartment->status,
                'governorate' => $apartment->governorate,
                'governorate_ar' => $apartment->governorate_ar,
                'city' => $apartment->city,
                'city_ar' => $apartment->city_ar,
                'address' => $apartment->address,
                'address_ar' => $apartment->address_ar,
                'nightly_price' => number_format($apartment->nightly_price, 2),
                'monthly_price' => number_format($apartment->monthly_price, 2),
                'bedrooms' => $apartment->bedrooms,
                'bathrooms' => $apartment->bathrooms,
                'living_rooms' => $apartment->living_rooms,
                'size' => number_format($apartment->size, 2),
                'photos' => $apartment->photos ? array_map(function ($photo) {
                    return '/storage/' . $photo;
                }, $apartment->photos) : [],
                'amenities' => $apartment->amenities ?? [],
                'total_bookings' => $totalBookings,
                'average_rating' => number_format($averageRating, 1),
                'created_at' => $apartment->created_at->toIso8601String(),
                'updated_at' => $apartment->updated_at->toIso8601String(),
                'owner' => [
                    'id' => $owner->id,
                    'first_name' => $owner->first_name,
                    'last_name' => $owner->last_name,
                    'mobile_number' => $owner->mobile_number,
                    'personal_photo' => $owner->personal_photo ? '/storage/' . $owner->personal_photo : null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Apartments retrieved successfully',
            'data' => [
                'apartments' => $apartmentsData,
                'pagination' => [
                    'current_page' => $apartments->currentPage(),
                    'last_page' => $apartments->lastPage(),
                    'per_page' => $apartments->perPage(),
                    'total' => $apartments->total(),
                ],
            ],
        ]);
    }
}

