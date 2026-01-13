<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventBooking;
use App\Enums\ResponseCode;

class EventBookingController extends Controller
{
    /**
     * List logged-in user's event bookings
     */
    public function index(Request $request)
    {
        try {

            $bookings = EventBooking::with([
                    'event'
                ])
                ->where('user_id', auth()->id()) //  only logged-in user
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
