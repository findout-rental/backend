<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * List all bookings for owner's apartments.
     * Supports filtering by status and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $owner = $request->user();
        $statusFilter = $request->query('status'); // 'pending', 'approved', 'history', or null for all
        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);

        // Get owner's apartment IDs
        $apartmentIds = Apartment::where('owner_id', $owner->id)->pluck('id');

        $query = Booking::whereIn('apartment_id', $apartmentIds)
            ->with(['apartment', 'tenant'])
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if ($statusFilter === 'pending') {
            // Pending and modified_pending
            $query->whereIn('status', ['pending', 'modified_pending']);
        } elseif ($statusFilter === 'approved') {
            // Approved and modified_approved
            $query->whereIn('status', ['approved', 'modified_approved']);
        } elseif ($statusFilter === 'history') {
            // Completed, cancelled, rejected, modified_rejected
            $query->whereIn('status', ['completed', 'cancelled', 'rejected', 'modified_rejected']);
        }

        $bookings = $query->paginate($perPage, ['*'], 'page', $page);

        $bookingsData = $bookings->map(function ($booking) {
            $apartment = $booking->apartment;
            $tenant = $booking->tenant;

            return [
                'id' => $booking->id,
                'status' => $booking->status,
                'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                'number_of_guests' => $booking->number_of_guests,
                'payment_method' => $booking->payment_method,
                'total_rent' => number_format($booking->total_rent, 2),
                'created_at' => $booking->created_at->toIso8601String(),
                'tenant' => [
                    'id' => $tenant->id,
                    'first_name' => $tenant->first_name,
                    'last_name' => $tenant->last_name,
                    'personal_photo' => $tenant->personal_photo ? '/storage/' . $tenant->personal_photo : null,
                    'mobile_number' => $tenant->mobile_number,
                ],
                'apartment' => [
                    'id' => $apartment->id,
                    'address' => $apartment->address,
                    'address_ar' => $apartment->address_ar,
                    'city' => $apartment->city,
                    'city_ar' => $apartment->city_ar,
                ],
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

    /**
     * Get booking details by ID.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function show(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $owner = $request->user();

        $booking = Booking::where('id', $id)
            ->with(['apartment' => function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            }, 'tenant'])
            ->first();

        if (!$booking || !$booking->apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $apartment = $booking->apartment;
        $tenant = $booking->tenant;
        $nights = \Carbon\Carbon::parse($booking->check_in_date)
            ->diffInDays(\Carbon\Carbon::parse($booking->check_out_date));

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => [
                    'id' => $booking->id,
                    'status' => $booking->status,
                    'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                    'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                    'duration_nights' => $nights,
                    'number_of_guests' => $booking->number_of_guests,
                    'payment_method' => $booking->payment_method,
                    'total_rent' => number_format($booking->total_rent, 2),
                    'created_at' => $booking->created_at->toIso8601String(),
                    'updated_at' => $booking->updated_at->toIso8601String(),
                    'tenant' => [
                        'id' => $tenant->id,
                        'first_name' => $tenant->first_name,
                        'last_name' => $tenant->last_name,
                        'personal_photo' => $tenant->personal_photo ? '/storage/' . $tenant->personal_photo : null,
                        'mobile_number' => $tenant->mobile_number,
                    ],
                    'apartment' => [
                        'id' => $apartment->id,
                        'governorate' => $apartment->governorate,
                        'governorate_ar' => $apartment->governorate_ar,
                        'city' => $apartment->city,
                        'city_ar' => $apartment->city_ar,
                        'address' => $apartment->address,
                        'address_ar' => $apartment->address_ar,
                        'photos' => $apartment->photos ? array_map(function ($photo) {
                            return '/storage/' . $photo;
                        }, $apartment->photos) : [],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Approve a booking request.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function approve(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $owner = $request->user();

        $booking = Booking::where('id', $id)
            ->with(['apartment' => function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            }, 'tenant'])
            ->first();

        if (!$booking || !$booking->apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if booking can be approved
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be approved.',
            ], 400);
        }

        try {
            $booking->update([
                'status' => 'approved',
            ]);

            // TODO: Create notification for tenant

            return response()->json([
                'success' => true,
                'message' => 'Booking approved successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Booking approval failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'owner_id' => $owner->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to approve booking. Please try again.',
            ], 500);
        }
    }

    /**
     * Reject a booking request.
     * Processes full refund (100% returned to tenant from owner).
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function reject(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $owner = $request->user();

        $booking = Booking::where('id', $id)
            ->with(['apartment' => function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            }, 'tenant'])
            ->first();

        if (!$booking || !$booking->apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if booking can be rejected
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be rejected.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Process full refund (100% returned to tenant)
            $tenant = $booking->tenant->refresh();
            $ownerUser = $owner->refresh();

            // Transfer full amount from owner back to tenant
            $refundAmount = $booking->total_rent;

            // Update balances
            $tenant->balance += $refundAmount;
            $tenant->save();

            $ownerUser->balance -= $refundAmount;
            $ownerUser->save();

            // Create transaction records
            \App\Models\Transaction::create([
                'user_id' => $tenant->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'related_booking_id' => $booking->id,
                'related_user_id' => $ownerUser->id,
                'description' => 'Full refund - Booking rejected by owner',
                'created_at' => now(),
            ]);

            \App\Models\Transaction::create([
                'user_id' => $ownerUser->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'related_booking_id' => $booking->id,
                'related_user_id' => $tenant->id,
                'description' => 'Refund issued - Booking rejected',
                'created_at' => now(),
            ]);

            // Update booking status
            $booking->update([
                'status' => 'rejected',
            ]);

            DB::commit();

            // TODO: Create notification for tenant

            return response()->json([
                'success' => true,
                'message' => 'Booking rejected and refund processed',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'refund_amount' => number_format($refundAmount, 2),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking rejection failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'owner_id' => $owner->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to reject booking. Please try again.',
            ], 500);
        }
    }

    /**
     * Approve a modification request.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function approveModification(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $owner = $request->user();

        $booking = Booking::where('id', $id)
            ->with(['apartment' => function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            }, 'tenant'])
            ->first();

        if (!$booking || !$booking->apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if modification can be approved
        if ($booking->status !== 'modified_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending modifications can be approved.',
            ], 400);
        }

        try {
            $booking->update([
                'status' => 'modified_approved',
            ]);

            // TODO: Create notification for tenant

            return response()->json([
                'success' => true,
                'message' => 'Modification approved successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                    'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Modification approval failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'owner_id' => $owner->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to approve modification. Please try again.',
            ], 500);
        }
    }

    /**
     * Reject a modification request.
     * Reverts booking to original approved status.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function rejectModification(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $owner = $request->user();

        $booking = Booking::where('id', $id)
            ->with(['apartment' => function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            }, 'tenant'])
            ->first();

        if (!$booking || !$booking->apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if modification can be rejected
        if ($booking->status !== 'modified_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending modifications can be rejected.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // TODO: Revert to original booking details if we stored them
            // For now, just change status back to approved
            // In a full implementation, we'd need to store original values
            
            $booking->update([
                'status' => 'approved',
            ]);

            // TODO: If rent was increased and paid, refund the difference
            // If rent was decreased, no additional action needed

            DB::commit();

            // TODO: Create notification for tenant

            return response()->json([
                'success' => true,
                'message' => 'Modification rejected. Booking reverted to approved status.',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Modification rejection failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'owner_id' => $owner->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to reject modification. Please try again.',
            ], 500);
        }
    }
}

