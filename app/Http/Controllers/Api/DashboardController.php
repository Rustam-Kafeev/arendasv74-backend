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

            // Активные авто – те, у которых хотя бы один город активен в pivot
            $activeCars = Car::where('user_id', $user->id)
                ->whereHas('cities', fn($q) => $q->where('is_available', true))
                ->count();

            $today = Carbon::today();
            $todayViews = CarView::whereHas('car', fn($q) => $q->where('user_id', $user->id))
                ->where('view_date', $today)->sum('count');

            $unreadMessages = Message::where('is_read', false)
                ->whereHas('conversation', fn($q) => $q->where('owner_id', $user->id)->orWhere('renter_id', $user->id))
                ->where('user_id', '!=', $user->id)->count();

            // График просмотров (последние 7 дней)
            $viewsChart = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->toDateString();
                $count = CarView::whereHas('car', fn($q) => $q->where('user_id', $user->id))
                    ->where('view_date', $date)->sum('count');
                $viewsChart[] = ['date' => $date, 'count' => $count];
            }

            // Последние сообщения
            $recentMessages = Message::whereHas('conversation', fn($q) => $q->where('owner_id', $user->id)->orWhere('renter_id', $user->id))
                ->where('user_id', '!=', $user->id)
                ->with(['user:id,name', 'conversation.car:id,brand,model'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($msg) => [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'created_at' => $msg->created_at->toDateTimeString(),
                    'user_name' => $msg->user->name,
                    'car_brand' => $msg->conversation->car->brand ?? '',
                    'car_model' => $msg->conversation->car->model ?? '',
                ]);

            return response()->json([
                'cars_count' => $carsCount,
                'active_cars' => $activeCars,
                'today_views' => $todayViews,
                'unread_messages' => $unreadMessages,
                'total_views' => $todayViews,
                'views_chart' => $viewsChart,
                'recent_messages' => $recentMessages,
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}