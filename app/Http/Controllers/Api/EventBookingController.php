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

        $filter = $request->query('filter');
        $clientDate = $request->query('event_date', now()->toDateString());

        // Base query with event and category
        $query = EventBooking::with(['event.category'])
            ->where('user_id', auth()->id());

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
            ->get()
            ->each(function ($booking) {

                //  Frontend flag
                $booking->isBooked = true;

                // Flatten event attributes into booking
                if ($booking->event) {
                    foreach ($booking->event->getAttributes() as $key => $value) {

                        // don't overwrite booking id
                        if ($key === 'id') {
                            continue;
                        }

                        $booking->{$key} = $value;
                    }

                    //  Keep category object
                    $booking->category = $booking->event->category;

                    // Remove event block
                    unset($booking->event);
                }
            });

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

        // Load booking with event and category
        $booking = EventBooking::with(['event.category'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$booking) {
            return $this->errorResponse(
                ResponseCode::NOT_FOUND,
                'BOOKING_NOT_FOUND'
            );
        }

        // Frontend flag
        $booking->isBooked = true;

        // Flatten event attributes into booking
        if ($booking->event) {
            foreach ($booking->event->getAttributes() as $key => $value) {

                // don't overwrite booking id
                if ($key === 'id') {
                    continue;
                }

                $booking->{$key} = $value;
            }

            // Keep category object
            $booking->category = $booking->event->category;

            // Remove event block
            unset($booking->event);
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
