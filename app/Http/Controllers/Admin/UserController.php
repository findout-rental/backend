<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        // Format response
        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'mobile_number' => $user->mobile_number,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'personal_photo' => $user->personal_photo ? Storage::url($user->personal_photo) : null,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $formattedUsers,
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

        // TODO: Send notification to user about approval

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
}
