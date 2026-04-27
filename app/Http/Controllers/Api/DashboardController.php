<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarView;
use App\Models\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $user = Auth::user();

        $carsCount = Car::where('user_id', $user->id)->count();
        $activeCars = Car::where('user_id', $user->id)->where('is_available', true)->count();

        $today = Carbon::today();
        $todayViews = CarView::whereHas('car', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('view_date', $today)->sum('count');

        $totalViews = CarView::whereHas('car', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->sum('count');

        // Просмотры за 7 дней
        $viewsChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $count = CarView::whereHas('car', fn($q) => $q->where('user_id', $user->id))
                ->where('view_date', $date)->sum('count');
            $viewsChart[] = ['date' => $date, 'count' => $count];
        }

        $unreadMessages = Message::where('is_read', false)
            ->whereHas('conversation', function ($q) use ($user) {
                $q->where('owner_id', $user->id)->orWhere('renter_id', $user->id);
            })
            ->where('user_id', '!=', $user->id)
            ->count();

        $recentMessages = Message::whereHas('conversation', function ($q) use ($user) {
            $q->where('owner_id', $user->id)->orWhere('renter_id', $user->id);
        })
            ->where('user_id', '!=', $user->id)
            ->with(['user:id,name', 'conversation.car:id,brand,model'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'created_at' => $msg->created_at->toDateTimeString(),
                    'user_name' => $msg->user->name,
                    'car_brand' => $msg->conversation->car->brand ?? '',
                    'car_model' => $msg->conversation->car->model ?? '',
                ];
            });

        return response()->json([
            'cars_count' => $carsCount,
            'active_cars' => $activeCars,
            'today_views' => $todayViews,
            'total_views' => $totalViews,
            'views_chart' => $viewsChart,
            'unread_messages' => $unreadMessages,
            'recent_messages' => $recentMessages,
        ]);
    }
}