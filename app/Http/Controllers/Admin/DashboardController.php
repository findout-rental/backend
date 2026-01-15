<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        // User statistics
        $totalUsers = User::count();
        $tenants = User::where('role', 'tenant')->count();
        $owners = User::where('role', 'owner')->count();
        $pendingUsers = User::where('status', 'pending')->count();

        // Apartment statistics
        $totalApartments = Apartment::count();
        $activeApartments = Apartment::where('status', 'active')->count();
        $inactiveApartments = Apartment::where('status', 'inactive')->count();

        // Booking statistics
        $totalBookings = Booking::count();
        $activeBookings = Booking::whereIn('status', ['pending', 'approved', 'modified_approved'])->count();
        $completedBookings = Booking::where('status', 'completed')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => [
                    'total' => $totalUsers,
                    'tenants' => $tenants,
                    'owners' => $owners,
                    'pending' => $pendingUsers,
                ],
                'apartments' => [
                    'total' => $totalApartments,
                    'active' => $activeApartments,
                    'inactive' => $inactiveApartments,
                ],
                'bookings' => [
                    'total' => $totalBookings,
                    'active' => $activeBookings,
                    'completed' => $completedBookings,
                ],
                'last_updated' => now()->toIso8601String(),
            ],
        ], 200);
    }
}

