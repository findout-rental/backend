<?php

namespace App\Services;

use App\Models\Apartment;
use Carbon\Carbon;

class BookingService
{
    /**
     * Calculate rent for a booking period.
     * Uses nightly price for stays <= 30 days, otherwise compares nightly vs monthly.
     *
     * @param Apartment $apartment
     * @param string $checkInDate
     * @param string $checkOutDate
     * @return float
     */
    public function calculateRent(Apartment $apartment, string $checkInDate, string $checkOutDate): float
    {
        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);

        $nights = $checkIn->diffInDays($checkOut);

        // For stays of 30 days or less, use nightly price
        if ($nights <= 30) {
            return $apartment->nightly_price * $nights;
        }

        // For longer stays, calculate both options and choose the better rate
        $dailyTotal = $apartment->nightly_price * $nights;
        $monthlyTotal = $apartment->monthly_price * ceil($nights / 30);

        return min($dailyTotal, $monthlyTotal);
    }

    /**
     * Check if there are any conflicting bookings for the given date range.
     * A conflict occurs if any approved or pending booking overlaps with the requested dates.
     *
     * @param int $apartmentId
     * @param string $checkInDate
     * @param string $checkOutDate
     * @param int|null $excludeBookingId Optional booking ID to exclude from conflict check (for modifications)
     * @return bool True if conflict exists, false otherwise
     */
    public function hasConflict(int $apartmentId, string $checkInDate, string $checkOutDate, ?int $excludeBookingId = null): bool
    {
        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);

        $query = \App\Models\Booking::where('apartment_id', $apartmentId)
            ->whereIn('status', ['pending', 'approved', 'modified_approved'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                // Check for overlapping bookings:
                // - Existing booking starts before requested checkout AND ends after requested checkin
                $q->where(function ($subQ) use ($checkIn, $checkOut) {
                    $subQ->where('check_in_date', '<', $checkOut->format('Y-m-d'))
                        ->where('check_out_date', '>', $checkIn->format('Y-m-d'));
                });
            });

        // Exclude a specific booking (useful for modifications)
        if ($excludeBookingId !== null) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }

    /**
     * Get conflicting bookings for the given date range.
     *
     * @param int $apartmentId
     * @param string $checkInDate
     * @param string $checkOutDate
     * @param int|null $excludeBookingId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConflictingBookings(int $apartmentId, string $checkInDate, string $checkOutDate, ?int $excludeBookingId = null)
    {
        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);

        $query = \App\Models\Booking::where('apartment_id', $apartmentId)
            ->whereIn('status', ['pending', 'approved', 'modified_approved'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($subQ) use ($checkIn, $checkOut) {
                    $subQ->where('check_in_date', '<', $checkOut->format('Y-m-d'))
                        ->where('check_out_date', '>', $checkIn->format('Y-m-d'));
                });
            });

        if ($excludeBookingId !== null) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get();
    }
}
