<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    /**
     * List all bookings in the system.
     * Supports filtering, searching, and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $statusFilter = $request->query('status');
        $search = $request->query('search');
        $checkInFrom = $request->query('check_in_from');
        $checkInTo = $request->query('check_in_to');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 25);
        $page = $request->query('page', 1);

        $query = Booking::with(['tenant', 'apartment.owner']);

        // Apply status filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // Apply search (booking ID, tenant name, apartment address)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('apartment', function ($q) use ($search) {
                        $q->where('address', 'like', "%{$search}%")
                            ->orWhere('address_ar', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('city_ar', 'like', "%{$search}%");
                    });
            });
        }

        // Apply date range filter
        if ($checkInFrom) {
            $query->where('check_in_date', '>=', $checkInFrom);
        }
        if ($checkInTo) {
            $query->where('check_in_date', '<=', $checkInTo);
        }

        // Apply sorting
        $allowedSortFields = ['id', 'created_at', 'check_in_date', 'check_out_date', 'total_rent', 'status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $bookings = $query->paginate($perPage, ['*'], 'page', $page);

        $bookingsData = $bookings->map(function ($booking) {
            $tenant = $booking->tenant;
            $apartment = $booking->apartment;
            $owner = $apartment->owner ?? null;

            return [
                'id' => $booking->id,
                'status' => $booking->status,
                'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                'number_of_guests' => $booking->number_of_guests,
                'payment_method' => $booking->payment_method,
                'total_rent' => number_format($booking->total_rent, 2),
                'created_at' => $booking->created_at->toIso8601String(),
                'updated_at' => $booking->updated_at->toIso8601String(),
                'tenant' => [
                    'id' => $tenant->id,
                    'first_name' => $tenant->first_name,
                    'last_name' => $tenant->last_name,
                    'mobile_number' => $tenant->mobile_number,
                    'personal_photo' => $tenant->personal_photo ? '/storage/' . $tenant->personal_photo : null,
                ],
                'apartment' => [
                    'id' => $apartment->id,
                    'address' => $apartment->address,
                    'address_ar' => $apartment->address_ar,
                    'city' => $apartment->city,
                    'city_ar' => $apartment->city_ar,
                    'governorate' => $apartment->governorate,
                    'governorate_ar' => $apartment->governorate_ar,
                ],
                'owner' => $owner ? [
                    'id' => $owner->id,
                    'first_name' => $owner->first_name,
                    'last_name' => $owner->last_name,
                    'mobile_number' => $owner->mobile_number,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Bookings retrieved successfully',
            'data' => [
                'bookings' => $bookingsData,
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                ],
            ],
        ]);
    }
}

