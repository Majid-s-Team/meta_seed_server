<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\EventBooking;
use App\Models\Livestream;
use App\Models\LivestreamBooking;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalEvents = Event::count();
        $upcomingEvents = Event::where('status', 'active')->whereDate('date', '>=', now()->toDateString())->count();
        $liveStreams = Livestream::where('status', 'live')->count();
        $liveViewersCount = Livestream::where('status', 'live')->sum('current_viewer_count');
        $ticketsSold = EventBooking::count();
        $totalRevenue = Transaction::where('type', 'credit')->sum('amount');
        $todayRevenue = Transaction::where('type', 'credit')->whereDate('created_at', today())->sum('amount');
        $walletPurchases = Transaction::where('type', 'credit')->count();
        $totalCommission = Transaction::sum('commission_amount');

        $ticketSalesTrend = EventBooking::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueTrend = Transaction::where('type', 'credit')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $userGrowthTrend = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $recentEventBookings = EventBooking::with(['user:id,name,email', 'event:id,title,date,time'])
            ->latest()
            ->take(10)
            ->get();

        $recentLivestreamBookings = LivestreamBooking::with(['user:id,name,email', 'livestream:id,title,scheduled_at'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalEvents', 'upcomingEvents', 'liveStreams', 'liveViewersCount',
            'ticketsSold', 'totalRevenue', 'todayRevenue', 'walletPurchases', 'totalCommission',
            'ticketSalesTrend', 'revenueTrend', 'userGrowthTrend',
            'recentEventBookings', 'recentLivestreamBookings'
        ));
    }
}
