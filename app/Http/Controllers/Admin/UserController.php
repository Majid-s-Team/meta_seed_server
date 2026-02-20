<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('wallet');
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        $users = $query->orderBy('id')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['wallet', 'livestreamBookings.livestream']);
        $eventBookings = \App\Models\EventBooking::where('user_id', $user->id)->with('event')->latest()->get();
        $transactions = \App\Models\Transaction::where('user_id', $user->id)->latest()->take(50)->get();
        return view('admin.users.show', compact('user', 'eventBookings', 'transactions'));
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot deactivate yourself.');
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }
}
