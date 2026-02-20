<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBooking;
use App\Models\Livestream;
use App\Models\LivestreamBooking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return view('admin.bookings.index');
    }

    public function eventBookings(Request $request)
    {
        $query = EventBooking::with(['user:id,name,email', 'event:id,title,date,time,category_id'])->with('event.category:id,name');
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('date')) {
            $query->whereHas('event', fn ($q) => $q->whereDate('date', $request->date));
        }
        $bookings = $query->latest()->paginate(20);
        $events = Event::orderBy('date', 'desc')->get(['id', 'title', 'date', 'coins']);
        $filteredRevenue = null;
        if ($request->filled('event_id')) {
            $ev = Event::find($request->event_id);
            if ($ev) {
                $count = EventBooking::where('event_id', $ev->id)->count();
                $filteredRevenue = ['event' => $ev->title, 'tickets' => $count, 'revenue' => $ev->coins * $count];
            }
        }
        return view('admin.bookings.event', compact('bookings', 'events', 'filteredRevenue'));
    }

    public function eventBookingsExport(Request $request)
    {
        $query = EventBooking::with(['user', 'event']);
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('date')) {
            $query->whereHas('event', fn ($q) => $q->whereDate('date', $request->date));
        }
        $bookings = $query->latest()->get();
        $headers = ['User', 'Email', 'Event', 'Event Date', 'Booked At'];
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, $headers);
        foreach ($bookings as $b) {
            fputcsv($csv, [
                $b->user->name ?? '',
                $b->user->email ?? '',
                $b->event->title ?? '',
                ($b->event->date ?? '') . ' ' . ($b->event->time ?? ''),
                $b->created_at?->format('Y-m-d H:i') ?? '',
            ]);
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="event-bookings-' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function livestreamBookings(Request $request)
    {
        $query = LivestreamBooking::with(['user', 'livestream']);
        if ($request->filled('livestream_id')) {
            $query->where('livestream_id', $request->livestream_id);
        }
        $bookings = $query->latest()->paginate(20);
        $livestreams = Livestream::orderBy('scheduled_at', 'desc')->get(['id', 'title', 'scheduled_at']);
        return view('admin.bookings.livestream', compact('bookings', 'livestreams'));
    }
}
