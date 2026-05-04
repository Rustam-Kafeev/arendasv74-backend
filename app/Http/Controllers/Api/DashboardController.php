<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarView;
use App\Models\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function stats()
    {
        try {
            $user = Auth::user();
            $carsCount = Car::where('user_id', $user->id)->count();
            $activeCars = Car::where('user_id', $user->id)->where('is_available', true)->count();

            $today = Carbon::today();
            $todayViews = CarView::whereHas('car', fn($q) => $q->where('user_id', $user->id))
                ->where('view_date', $today)->sum('count');

            $unreadMessages = Message::where('is_read', false)
                ->whereHas('conversation', fn($q) => $q->where('owner_id', $user->id)->orWhere('renter_id', $user->id))
                ->where('user_id', '!=', $user->id)->count();

            return response()->json([
                'cars_count' => $carsCount,
                'active_cars' => $activeCars,
                'today_views' => $todayViews,
                'unread_messages' => $unreadMessages,
                'total_views' => $todayViews,
                'views_chart' => [],
                'recent_messages' => [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}