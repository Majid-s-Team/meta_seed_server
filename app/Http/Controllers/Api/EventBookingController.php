<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventBooking;
use App\Constants\ResponseCode;
use App\Traits\ApiResponseTrait;
use App\Models\EventCategory;

class EventBookingController extends Controller
{
    use ApiResponseTrait;
    /**
     * List logged-in user's event bookings
     */
   public function index(Request $request)
{
    try {

        $filter = $request->query('filter'); // upcoming | past
        $clientDate = $request->query('event_date', now()->toDateString());

        $query = EventBooking::with(['event'])
            ->where('user_id', auth()->id()); // 🔐 only logged-in user

        // 📅 Date filter (event date ke basis par)
        if ($filter === 'upcoming' && $clientDate) {
            $query->whereHas('event', function ($q) use ($clientDate) {
                $q->whereDate('date', '>', $clientDate);
            });
        }
        elseif ($filter === 'past' && $clientDate) {
            $query->whereHas('event', function ($q) use ($clientDate) {
                $q->whereDate('date', '<', $clientDate);
            });
        }

        $bookings = $query
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse('SUCCESS', $bookings);

    } catch (\Exception $e) {
        return $this->errorResponse(
            ResponseCode::INTERNAL_SERVER_ERROR,
            'SERVER_ERROR'
        );
    }
}


 /**
     * Show single event booking (only logged-in user)
     */
    public function show($id)
    {
        try {

            $booking = EventBooking::with(['event'])
                ->where('id', $id)
                ->where('user_id', auth()->id()) // security check
                ->first();

            if (!$booking) {
                return $this->errorResponse(
                    ResponseCode::NOT_FOUND,
                    'BOOKING_NOT_FOUND'
                );
            }

            return $this->successResponse('SUCCESS', $booking);

        } catch (\Exception $e) {
            return $this->errorResponse(
                ResponseCode::INTERNAL_SERVER_ERROR,
                'SERVER_ERROR'
            );
        }
    }





}
