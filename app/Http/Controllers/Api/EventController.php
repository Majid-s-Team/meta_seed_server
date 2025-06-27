<?php

namespace App\Http\Controllers\Api;

use App\Models\{Event, EventCategory, EventBooking, Wallet, Transaction};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
   public function index(Request $request)
{
    $filter = $request->query('filter'); 
    $clientDate = $request->query('event_date', now()->toDateString());
    $query = Event::with('category');

    if ($filter === 'upcoming' && $clientDate) {
        $query->whereDate('date', '>', $clientDate);
    } elseif ($filter === 'past' && $clientDate) {
        $query->whereDate('date', '<', $clientDate);
    }

    if (auth()->user()->role === 'user') {
        $query->whereIn('status', ['active', 'completed']);
    }

    return response()->json($query->orderBy('date', 'asc')->get());
}



    public function show($id)
    {
        return response()->json(Event::with('category')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'date' => 'required|date',
            'time' => 'required',
            'total_seats' => 'required|integer',
            'available_seats' => 'required|integer',
            'coins' => 'required|integer',
            'category_id' => 'required|exists:event_categories,id',
            'is_online' => 'required|boolean'
        ]);

        $event = Event::create($data);
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes',
            'description' => 'nullable',
            'date' => 'sometimes|date',
            'time' => 'sometimes',
            'total_seats' => 'sometimes|integer',
            'available_seats' => 'sometimes|integer',
            'coins' => 'sometimes|integer',
            'category_id' => 'sometimes|exists:event_categories,id',
            'is_online' => 'sometimes|boolean'
        ]);

        $event->update($data);
        return response()->json($event);
    }

    public function changeStatus(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive,completed']);

        $event->status = $request->status;
        $event->save();

        return response()->json(['status' => 'updated']);
    }

    public function book($id)
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();

        if ($event->available_seats <= 0) {
            return response()->json(['message' => 'No seats available'], 400);
        }

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        if ($wallet->balance < $event->coins) {
            return response()->json(['message' => 'Insufficient coins'], 400);
        }

        EventBooking::create([
            'user_id' => $user->id,
            'event_id' => $event->id
        ]);

        $event->available_seats -= 1;
        $event->save();

        $wallet->balance -= $event->coins;
        $wallet->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => $event->coins,
            'description' => 'Event booking: ' . $event->title
        ]);

        return response()->json(['message' => 'Booked successfully']);
    }
}
