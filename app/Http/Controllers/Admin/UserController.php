<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\RejectUserRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * List all users with their status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by role if provided
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Search by name or mobile number
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('first_name', 'desc')->orderBy('last_name', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 50);
        $users = $query->paginate($perPage);

        // Calculate statistics
        $statistics = [
            'total' => User::count(),
            'approved' => User::where('status', 'approved')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'rejected' => User::where('status', 'rejected')->count(),
        ];

        // Format response
        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'mobile_number' => $user->mobile_number,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'personal_photo' => $user->personal_photo ? Storage::url($user->personal_photo) : null,
                'id_photo' => $user->id_photo ? Storage::url($user->id_photo) : null,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $formattedUsers,
                'statistics' => $statistics,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Approve a user registration.
     *
     * @param int $user_id
     * @return JsonResponse
     */
    public function approve(int $user_id): JsonResponse
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($user->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'User is already approved',
            ], 400);
        }

        $user->status = 'approved';
        $user->save();

        // Send notification to user about approval
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->create($user, 'account_approved');

        return response()->json([
            'success' => true,
            'message' => 'User approved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'status' => $user->status,
                ],
            ],
        ], 200);
    }

    /**
     * Reject a user registration.
     *
     * @param int $user_id
     * @param RejectUserRequest $request
     * @return JsonResponse
     */
    public function reject(int $user_id, RejectUserRequest $request): JsonResponse
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($user->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'User is already rejected',
            ], 400);
        }

        if ($user->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an approved user',
            ], 400);
        }

        $user->status = 'rejected';
        $user->save();

        // Send notification to user about rejection
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->create($user, 'account_rejected');

        return response()->json([
            'success' => true,
            'message' => 'User rejected successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'status' => $user->status,
                    'rejection_reason' => $request->reason,
                ],
            ],
        ], 200);
    }

    /**
     * Get user's balance.
     *
     * @param int $user_id
     * @return JsonResponse
     */
    public function getBalance(int $user_id): JsonResponse
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $user->balance,
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Deposit money to user's balance.
     *
     * @param int $user_id
     * @param DepositRequest $request
     * @return JsonResponse
     */
    public function deposit(int $user_id, DepositRequest $request): JsonResponse
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $amount = (float) $request->amount;
            $description = $request->description ?? 'Cash deposit from admin';

            // Update user balance
            $user->balance = (float) $user->balance + $amount;
            $user->save();

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $amount,
                'description' => $description,
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Money added successfully',
                'data' => [
                    'new_balance' => (float) $user->balance,
                    'transaction_id' => $transaction->id,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process deposit. Please try again.',
            ], 500);
        }
    }

    /**
     * Withdraw money from user's balance.
     *
     * @param int $user_id
     * @param WithdrawRequest $request
     * @return JsonResponse
     */
    public function withdraw(int $user_id, WithdrawRequest $request): JsonResponse
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $amount = (float) $request->amount;

        // Check if user has sufficient balance
        if ($user->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Available: ' . number_format($user->balance, 2) . ', Requested: ' . number_format($amount, 2),
            ], 400);
        }

        try {
            DB::beginTransaction();

            $description = $request->description ?? 'Cash withdrawal by admin';

            // Update user balance
            $user->balance = (float) $user->balance - $amount;
            $user->save();

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'description' => $description,
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Money withdrawn successfully',
                'data' => [
                    'new_balance' => (float) $user->balance,
                    'transaction_id' => $transaction->id,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process withdrawal. Please try again.',
            ], 500);
        }
    }

    /**
     * Get user's transaction history.
     *
     * @param int $user_id
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactions(int $user_id, Request $request): JsonResponse
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Get query parameters
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $sort = $request->get('sort', 'newest');

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

    /**
     * Upload photo for a user (admin only).
     *
     * @param int $user_id
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPhoto(int $user_id, Request $request): JsonResponse
    {
        $request->validate([
            'photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB
            ],
        ]);

        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Delete old photo if exists
        if ($user->personal_photo && Storage::disk('public')->exists($user->personal_photo)) {
            Storage::disk('public')->delete($user->personal_photo);
        }

        // Store new photo
        $photoPath = $request->file('photo')->store('users/photos', 'public');
        $user->update(['personal_photo' => $photoPath]);

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'personal_photo' => Storage::url($photoPath),
                ],
            ],
        ], 200);
    }
}
