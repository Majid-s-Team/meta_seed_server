<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Wallet, Transaction, EventBooking};
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 1. List all users with wallet balances
    public function listUsers()
    {
        $users = User::with('wallet')->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'wallet_balance' => optional($user->wallet)->balance ?? 0,
            ];
        });

        return response()->json($data);
    }

    // 2. All transactions of all users
    public function allTransactions()
    {
        $transactions = Transaction::with('user')->latest()->get();

        return response()->json($transactions->map(function ($txn) {
            return [
                'user' => $txn->user->name,
                'amount' => $txn->amount,
                'type' => $txn->type,
                'description' => $txn->description,
                'date' => $txn->created_at->toDateTimeString(),
            ];
        }));
    }

    // 3. Toggle user active/inactive
    public function toggleUserActive($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => 'User status updated',
            'user_id' => $user->id,
            'is_active' => $user->is_active
        ]);
    }

    //  4. Analytics summary
    public function analytics()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_coins' => Wallet::sum('balance'),
            'total_transactions' => Transaction::count(),
            'total_credits' => Transaction::where('type', 'credit')->sum('amount'),
            'total_debits' => Transaction::where('type', 'debit')->sum('amount'),
            'most_booked_user' => EventBooking::select('user_id', DB::raw('count(*) as total'))
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->with('user')
                ->first()
        ]);
    }
    public function bookingHistory(Request $request)
    {
        $query = EventBooking::with(['user', 'event']);

        // Optional filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Optional filter by event_id
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $bookings = $query->latest()->get();

        return response()->json($bookings->map(function ($booking) {
            return [
                'booking_id' => $booking->id,
                'user' => [
                    'id' => $booking->user->id,
                    'name' => $booking->user->name,
                    'email' => $booking->user->email,
                ],
                'event' => [
                    'id' => $booking->event->id,
                    'title' => $booking->event->title,
                    'date' => $booking->event->date,
                    'time' => $booking->event->time,
                ],
                'booked_at' => $booking->created_at->toDateTimeString()
            ];
        }));
    }
}
