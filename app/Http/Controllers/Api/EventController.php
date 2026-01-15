<?php

namespace App\Http\Controllers\Api;

use App\Models\{Event, EventCategory, EventBooking, Wallet, Transaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;

class EventController extends Controller
{
    use ApiResponseTrait;

    /**
     * List events with filters (upcoming/past)
     */
   use App\Models\EventBooking;

public function index(Request $request)
{
    try {
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

        $events = $query->orderBy('date', 'asc')->get();

        // isBooked add (structure same)
        if (auth()->check()) {
            $bookedEventIds = EventBooking::where('user_id', auth()->id())
                ->pluck('event_id')
                ->toArray();

            $events->transform(function ($event) use ($bookedEventIds) {
                $event->isBooked = in_array($event->id, $bookedEventIds);
                return $event;
            });
        }

        return $this->successResponse('SUCCESS', $events);

    } catch (\Exception $e) {
        return $this->errorResponse(
            ResponseCode::INTERNAL_SERVER_ERROR,
            'SERVER_ERROR'
        );
    }
}


    /**
     * Show event details
     */
    public function show($id)
    {
        try {
            $event = Event::with('category')->find($id);

            if (!$event) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            return $this->successResponse('SUCCESS', $event);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Create a new event
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'time' => 'required',
                'total_seats' => 'required|integer',
                'available_seats' => 'required|integer',
                'coins' => 'required|integer',
                'category_id' => 'required|exists:event_categories,id',
                'is_online' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'FAILED', $validator->errors());
            }

            $event = Event::create($validator->validated());

            return $this->apiResponse(ResponseCode::CREATED, 'SUCCESS', $event);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Update event details
     */
    public function update(Request $request, $id)
    {
        try {
            $event = Event::find($id);
            if (!$event) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string',
                'description' => 'nullable|string',
                'date' => 'sometimes|date',
                'time' => 'sometimes',
                'total_seats' => 'sometimes|integer',
                'available_seats' => 'sometimes|integer',
                'coins' => 'sometimes|integer',
                'category_id' => 'sometimes|exists:event_categories,id',
                'is_online' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'FAILED', $validator->errors());
            }

            $event->update($validator->validated());

            return $this->successResponse('SUCCESS', $event);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Change event status
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $event = Event::find($id);
            if (!$event) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,completed',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(ResponseCode::VALIDATION_ERROR, 'FAILED', $validator->errors());
            }

            $event->status = $request->status;
            $event->save();

            return $this->successResponse('SUCCESS', $event);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Book an event
     */
    public function book($id)
    {
        try {
            $event = Event::find($id);
            $user = Auth::user();

            if (!$event) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'USER_NOT_FOUND');
            }

            if ($event->available_seats <= 0) {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'FAILED', ['message' => 'No seats available']);
            }

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

            if ($wallet->balance < $event->coins) {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'FAILED', ['message' => 'Insufficient coins']);
            }

            EventBooking::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
            ]);

            $event->decrement('available_seats');
            $wallet->decrement('balance', $event->coins);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $event->coins,
                'description' => 'Event booking: ' . $event->title,
            ]);

            return $this->successResponse('SUCCESS', ['message' => 'Event booked successfully']);

        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }
}
