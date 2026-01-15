<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /**
     * Get current user's balance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBalance(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $user->balance,
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Get user's transaction history.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get query parameters
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type'); // deposit, withdrawal, rent_payment, refund, cancellation_fee
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $sort = $request->get('sort', 'newest'); // newest, oldest

        // Build query
        $query = Transaction::where('user_id', $user->id)
            ->with(['booking.apartment', 'relatedUser']);

        // Filter by type
        if ($type) {
            $query->where('type', $type);
        }

        // Filter by date range
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sort
        $sortOrder = $sort === 'oldest' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortOrder);

        // Get transactions with pagination
        $transactions = $query->paginate($perPage);

        // Format transactions
        $formattedTransactions = $transactions->map(function ($transaction) {
            $data = [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => (float) $transaction->amount,
                'description' => $transaction->description,
                'created_at' => $transaction->created_at->toIso8601String(),
            ];

            // Add related booking if exists
            if ($transaction->booking) {
                $data['booking'] = [
                    'id' => $transaction->booking->id,
                    'apartment' => $transaction->booking->apartment ? [
                        'id' => $transaction->booking->apartment->id,
                        'title' => $transaction->booking->apartment->title,
                        'address' => $transaction->booking->apartment->address,
                    ] : null,
                ];
            }

            // Add related user if exists
            if ($transaction->relatedUser) {
                $data['related_user'] = [
                    'id' => $transaction->relatedUser->id,
                    'first_name' => $transaction->relatedUser->first_name,
                    'last_name' => $transaction->relatedUser->last_name,
                ];
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $user->balance,
                'transactions' => $formattedTransactions,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ],
            ],
        ], 200);
    }
}

