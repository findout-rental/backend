<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRatingRequest;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    /**
     * Create a rating for a completed booking.
     * Only allows rating after check-out date has passed and booking is completed.
     *
     * @param StoreRatingRequest $request
     * @return JsonResponse
     */
    public function store(StoreRatingRequest $request): JsonResponse
    {
        $tenant = $request->user();

        // Get the booking
        $booking = Booking::where('id', $request->booking_id)
            ->where('tenant_id', $tenant->id)
            ->with('apartment')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found or you do not have permission to rate this booking',
            ], 404);
        }

        // Check if booking is completed
        if ($booking->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'You can only rate completed bookings. This booking status is: ' . $booking->status,
            ], 400);
        }

        // Check if check-out date has passed
        $checkOutDate = \Carbon\Carbon::parse($booking->check_out_date)->startOfDay();
        $today = now()->startOfDay();
        
        if ($checkOutDate->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only rate after the check-out date has passed.',
            ], 400);
        }

        // Check if rating already exists for this booking
        $existingRating = Rating::where('booking_id', $booking->id)->first();
        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this booking',
            ], 400);
        }

        try {
            // Create rating
            $rating = Rating::create([
                'booking_id' => $booking->id,
                'apartment_id' => $booking->apartment_id,
                'tenant_id' => $tenant->id,
                'rating' => $request->rating,
                'review_text' => $request->review_text,
            ]);

            // Refresh apartment to get updated average rating
            $apartment = $booking->apartment->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'data' => [
                    'rating_id' => $rating->id,
                    'booking_id' => $booking->id,
                    'apartment_id' => $apartment->id,
                    'rating' => $rating->rating,
                    'review_text' => $rating->review_text,
                    'apartment_average_rating' => number_format($apartment->average_rating, 1),
                    'created_at' => $rating->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Rating creation failed: ' . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'booking_id' => $booking->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to submit rating. Please try again.',
            ], 500);
        }
    }
}

