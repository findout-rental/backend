<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Apartment;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected PaymentService $paymentService;

    public function __construct(BookingService $bookingService, PaymentService $paymentService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
    }

    /**
     * List all bookings for the authenticated tenant.
     * Supports filtering by status (current, past, cancelled) and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $tenant = $request->user();
        $statusFilter = $request->query('status'); // 'current', 'past', 'cancelled', or null for all
        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);

        $query = Booking::where('tenant_id', $tenant->id)
            ->with(['apartment' => function ($q) {
                $q->select('id', 'governorate', 'governorate_ar', 'city', 'city_ar', 'address', 'address_ar', 'photos');
            }])
            ->orderBy('created_at', 'desc');

        // Apply status filter
        $today = now()->startOfDay();
        if ($statusFilter === 'current') {
            // Current bookings: approved, pending, modified_pending, modified_approved
            // where check_out_date is in the future
            $query->whereIn('status', ['pending', 'approved', 'modified_pending', 'modified_approved'])
                ->where('check_out_date', '>=', $today);
        } elseif ($statusFilter === 'past') {
            // Past bookings: completed or check_out_date is in the past
            $query->where(function ($q) use ($today) {
                $q->where('status', 'completed')
                    ->orWhere('check_out_date', '<', $today);
            });
        } elseif ($statusFilter === 'cancelled') {
            // Cancelled bookings
            $query->whereIn('status', ['cancelled', 'rejected', 'modified_rejected']);
        }

        $bookings = $query->paginate($perPage, ['*'], 'page', $page);

        $bookingsData = $bookings->map(function ($booking) {
            $apartment = $booking->apartment;
            $checkIn = \Carbon\Carbon::parse($booking->check_in_date);
            $checkOut = \Carbon\Carbon::parse($booking->check_out_date);
            $today = now()->startOfDay();
            
            // Determine available actions
            $canCancel = in_array($booking->status, ['pending', 'approved', 'modified_approved']) 
                && $checkIn->diffInHours($today) >= 24;
            $canModify = in_array($booking->status, ['approved', 'modified_approved'])
                && $checkIn->diffInHours($today) >= 24;
            $canRate = $booking->status === 'completed' && !$booking->rating;

            return [
                'id' => $booking->id,
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
                'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                'status' => $booking->status,
                'number_of_guests' => $booking->number_of_guests,
                'payment_method' => $booking->payment_method,
                'total_rent' => number_format($booking->total_rent, 2),
                'can_cancel' => $canCancel,
                'can_modify' => $canModify,
                'can_rate' => $canRate,
                'created_at' => $booking->created_at->toIso8601String(),
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
        $tenant = $request->user();

        $booking = Booking::where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->with(['apartment.owner', 'rating'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $apartment = $booking->apartment;
        $checkIn = \Carbon\Carbon::parse($booking->check_in_date);
        $checkOut = \Carbon\Carbon::parse($booking->check_out_date);
        $today = now()->startOfDay();
        
        // Determine available actions
        $canCancel = in_array($booking->status, ['pending', 'approved', 'modified_approved']) 
            && $checkIn->diffInHours($today) >= 24;
        $canModify = in_array($booking->status, ['approved', 'modified_approved'])
            && $checkIn->diffInHours($today) >= 24;
        $canRate = $booking->status === 'completed' && !$booking->rating;

        $nights = $checkIn->diffInDays($checkOut);

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
                    'can_cancel' => $canCancel,
                    'can_modify' => $canModify,
                    'can_rate' => $canRate,
                    'created_at' => $booking->created_at->toIso8601String(),
                    'updated_at' => $booking->updated_at->toIso8601String(),
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
                        'nightly_price' => number_format($apartment->nightly_price, 2),
                        'monthly_price' => number_format($apartment->monthly_price, 2),
                        'bedrooms' => $apartment->bedrooms,
                        'bathrooms' => $apartment->bathrooms,
                        'living_rooms' => $apartment->living_rooms,
                        'size' => number_format($apartment->size, 2),
                        'amenities' => $apartment->amenities ?? [],
                        'owner' => [
                            'id' => $apartment->owner->id,
                            'first_name' => $apartment->owner->first_name,
                            'last_name' => $apartment->owner->last_name,
                            'personal_photo' => $apartment->owner->personal_photo ? '/storage/' . $apartment->owner->personal_photo : null,
                        ],
                    ],
                    'rating' => $booking->rating ? [
                        'id' => $booking->rating->id,
                        'rating' => $booking->rating->rating,
                        'review_text' => $booking->rating->review_text,
                        'created_at' => $booking->rating->created_at->toIso8601String(),
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Create a new booking.
     * Checks for conflicts, calculates rent, processes payment, and creates booking.
     *
     * @param StoreBookingRequest $request
     * @return JsonResponse
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $tenant = $request->user();

        // Verify tenant has sufficient balance (preliminary check)
        // We'll do a proper check after calculating rent
        $apartment = Apartment::where('id', $request->apartment_id)
            ->where('status', 'active')
            ->with('owner')
            ->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found or not available',
            ], 404);
        }

        // Check for date conflicts
        if ($this->bookingService->hasConflict(
            $apartment->id,
            $request->check_in_date,
            $request->check_out_date
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Selected dates are no longer available. Please select different dates.',
            ], 409);
        }

        // Calculate total rent
        $totalRent = $this->bookingService->calculateRent(
            $apartment,
            $request->check_in_date,
            $request->check_out_date
        );

        // Check tenant has sufficient balance
        if ($tenant->balance < $totalRent) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Required: ' . number_format($totalRent, 2) . ', Available: ' . number_format($tenant->balance, 2),
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create booking
            $booking = Booking::create([
                'tenant_id' => $tenant->id,
                'apartment_id' => $apartment->id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'number_of_guests' => $request->number_of_guests,
                'payment_method' => $request->payment_method,
                'total_rent' => $totalRent,
                'status' => 'pending',
            ]);

            // Process payment (deduct from tenant, add to owner)
            $paymentResult = $this->paymentService->processRentPayment(
                $tenant->refresh(), // Refresh to get latest balance
                $apartment->owner,
                $totalRent,
                $booking->id
            );

            if (!$paymentResult['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['message'],
                ], 400);
            }

            DB::commit();

            // TODO: Create notifications for tenant and owner
            // Notification::create(...) for tenant confirmation
            // Notification::create(...) for owner booking request

            return response()->json([
                'success' => true,
                'message' => 'Booking request submitted successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'total_rent' => number_format($booking->total_rent, 2),
                    'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                    'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                    'remaining_balance' => number_format($tenant->refresh()->balance, 2),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'apartment_id' => $apartment->id,
                'check_in' => $request->check_in_date,
                'check_out' => $request->check_out_date,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create booking. Please try again.',
            ], 500);
        }
    }

    /**
     * Modify a booking.
     * Updates booking details and changes status to "modified_pending" for owner approval.
     *
     * @param int $id
     * @param UpdateBookingRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateBookingRequest $request): JsonResponse
    {
        $tenant = $request->user();

        $booking = Booking::where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->with('apartment')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if booking can be modified
        if (!in_array($booking->status, ['approved', 'modified_approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be modified. Only approved bookings can be modified.',
            ], 400);
        }

        // Check if modification is allowed (at least 24 hours before check-in)
        $checkIn = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
        $today = now();
        $hoursUntilCheckIn = abs($checkIn->diffInHours($today));
        if ($hoursUntilCheckIn < 24) {
            return response()->json([
                'success' => false,
                'message' => 'Modification is only allowed at least 24 hours before check-in.',
            ], 400);
        }

        // Check if any changes were made
        $hasChanges = false;
        $newCheckIn = $request->input('check_in_date');
        $newCheckOut = $request->input('check_out_date');
        $newGuests = $request->input('number_of_guests');

        if ($newCheckIn && $newCheckIn !== $booking->check_in_date->format('Y-m-d')) {
            $hasChanges = true;
        }
        if ($newCheckOut && $newCheckOut !== $booking->check_out_date->format('Y-m-d')) {
            $hasChanges = true;
        }
        if ($newGuests !== null && $newGuests != $booking->number_of_guests) {
            $hasChanges = true;
        }

        if (!$hasChanges) {
            return response()->json([
                'success' => false,
                'message' => 'Please make changes before submitting modification request.',
            ], 400);
        }

        $apartment = $booking->apartment;
        $finalCheckIn = $newCheckIn ?? $booking->check_in_date->format('Y-m-d');
        $finalCheckOut = $newCheckOut ?? $booking->check_out_date->format('Y-m-d');

        // Check for date conflicts (excluding current booking)
        if ($this->bookingService->hasConflict(
            $apartment->id,
            $finalCheckIn,
            $finalCheckOut,
            $booking->id // Exclude current booking from conflict check
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Selected dates are no longer available. Please select different dates.',
            ], 409);
        }

        try {
            DB::beginTransaction();

            // Calculate new rent if dates changed
            $newTotalRent = $booking->total_rent;
            if ($newCheckIn || $newCheckOut) {
                $newTotalRent = $this->bookingService->calculateRent(
                    $apartment,
                    $finalCheckIn,
                    $finalCheckOut
                );
            }

            // Handle rent difference
            $rentDifference = $newTotalRent - $booking->total_rent;
            
            if ($rentDifference > 0) {
                // Tenant needs to pay more
                if ($tenant->balance < $rentDifference) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance. Additional amount required: ' . number_format($rentDifference, 2),
                    ], 400);
                }

                // Process additional payment
                $paymentResult = $this->paymentService->processRentPayment(
                    $tenant->refresh(),
                    $apartment->owner,
                    $rentDifference,
                    $booking->id
                );

                if (!$paymentResult['success']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $paymentResult['message'],
                    ], 400);
                }
            } elseif ($rentDifference < 0) {
                // Tenant gets refund (owner approval will process full refund if needed)
                // For now, just update the booking. Refund will be handled when owner approves/rejects
            }

            // Update booking
            $booking->update([
                'check_in_date' => $finalCheckIn,
                'check_out_date' => $finalCheckOut,
                'number_of_guests' => $newGuests ?? $booking->number_of_guests,
                'total_rent' => $newTotalRent,
                'status' => 'modified_pending',
            ]);

            DB::commit();

            // TODO: Create notification for owner about modification request

            return response()->json([
                'success' => true,
                'message' => 'Modification request sent to owner',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                    'check_out_date' => $booking->check_out_date->format('Y-m-d'),
                    'total_rent' => number_format($booking->total_rent, 2),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking modification failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'tenant_id' => $tenant->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to modify booking. Please try again.',
            ], 500);
        }
    }

    /**
     * Cancel a booking.
     * Cancels the booking and processes partial refund (80% to tenant, 20% cancellation fee).
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function cancel(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $tenant = $request->user();

        $booking = Booking::where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->with('apartment.owner')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if booking can be cancelled
        if (!in_array($booking->status, ['pending', 'approved', 'modified_approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled.',
            ], 400);
        }

        // Check if cancellation is allowed (at least 24 hours before check-in)
        $checkIn = \Carbon\Carbon::parse($booking->check_in_date)->startOfDay();
        $today = now();
        $hoursUntilCheckIn = abs($checkIn->diffInHours($today));
        if ($hoursUntilCheckIn < 24) {
            return response()->json([
                'success' => false,
                'message' => 'Cancellation is only allowed at least 24 hours before check-in.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Process refund (80% to tenant, 20% cancellation fee)
            $refundResult = $this->paymentService->processRefund(
                $tenant->refresh(),
                $booking->apartment->owner,
                $booking->total_rent,
                $booking->id
            );

            if (!$refundResult['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $refundResult['message'],
                ], 400);
            }

            // Update booking status
            $booking->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            // TODO: Create notifications for tenant and owner

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'refund_amount' => number_format($refundResult['refund_amount'], 2),
                    'cancellation_fee' => number_format($refundResult['cancellation_fee'], 2),
                    'remaining_balance' => number_format($tenant->refresh()->balance, 2),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking cancellation failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'tenant_id' => $tenant->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to cancel booking. Please try again.',
            ], 500);
        }
    }
}


