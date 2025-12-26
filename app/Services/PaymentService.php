<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Process rent payment when booking is created.
     * Deducts from tenant balance and adds to owner balance.
     * Creates transaction records for both users.
     *
     * @param User $tenant
     * @param User $owner
     * @param float $amount
     * @param int $bookingId
     * @return array Returns ['success' => bool, 'message' => string]
     */
    public function processRentPayment(User $tenant, User $owner, float $amount, int $bookingId): array
    {
        try {
            DB::beginTransaction();

            // Check tenant has sufficient balance
            if ($tenant->balance < $amount) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Insufficient balance. Required: ' . number_format($amount, 2) . ', Available: ' . number_format($tenant->balance, 2),
                ];
            }

            // Update tenant balance (decrease)
            $tenant->balance -= $amount;
            $tenant->save();

            // Update owner balance (increase)
            $owner->balance += $amount;
            $owner->save();

            // Create transaction for tenant (debit)
            Transaction::create([
                'user_id' => $tenant->id,
                'type' => 'rent_payment',
                'amount' => $amount,
                'related_booking_id' => $bookingId,
                'related_user_id' => $owner->id,
                'description' => 'Rent payment for booking #' . $bookingId,
                'created_at' => now(),
            ]);

            // Create transaction for owner (credit)
            Transaction::create([
                'user_id' => $owner->id,
                'type' => 'rent_payment',
                'amount' => $amount,
                'related_booking_id' => $bookingId,
                'related_user_id' => $tenant->id,
                'description' => 'Rent received from booking #' . $bookingId,
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment processed successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund when booking is cancelled or rejected.
     * Refunds 80% to tenant, owner keeps 20% as cancellation fee.
     *
     * @param User $tenant
     * @param User $owner
     * @param float $totalRent
     * @param int $bookingId
     * @return array
     */
    public function processRefund(User $tenant, User $owner, float $totalRent, int $bookingId): array
    {
        try {
            DB::beginTransaction();

            $refundAmount = $totalRent * 0.80; // 80% refund
            $cancellationFee = $totalRent * 0.20; // 20% fee

            // Update tenant balance (increase)
            $tenant->balance += $refundAmount;
            $tenant->save();

            // Update owner balance (decrease by refund amount)
            $owner->balance -= $refundAmount;
            $owner->save();

            // Create transaction for tenant (refund)
            Transaction::create([
                'user_id' => $tenant->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'related_booking_id' => $bookingId,
                'related_user_id' => $owner->id,
                'description' => 'Partial refund - Booking cancelled (80% of ' . number_format($totalRent, 2) . ')',
                'created_at' => now(),
            ]);

            // Create transaction for owner (refund deduction)
            Transaction::create([
                'user_id' => $owner->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'related_booking_id' => $bookingId,
                'related_user_id' => $tenant->id,
                'description' => 'Refund issued - Booking cancelled (80% returned)',
                'created_at' => now(),
            ]);

            // Create transaction record for cancellation fee (audit only, no balance change)
            Transaction::create([
                'user_id' => $owner->id,
                'type' => 'cancellation_fee',
                'amount' => $cancellationFee,
                'related_booking_id' => $bookingId,
                'related_user_id' => $tenant->id,
                'description' => 'Cancellation fee - Owner keeps 20% (' . number_format($cancellationFee, 2) . ')',
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Refund processed successfully',
                'refund_amount' => $refundAmount,
                'cancellation_fee' => $cancellationFee,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage(),
            ];
        }
    }
}


