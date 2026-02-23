<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('category')->withCount('bookings')->orderBy('date', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $events = $query->paginate(15);
        $categories = EventCategory::orderBy('name')->get();
        return view('admin.events.index', compact('events', 'categories'));
    }

    public function create()
    {
        $categories = EventCategory::orderBy('name')->get();
        return view('admin.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required|string|max:20',
            'total_seats' => 'required|integer|min:1',
            'available_seats' => 'required|integer|min:0',
            'coins' => 'required|integer|min:0',
            'category_id' => 'required|exists:event_categories,id',
            'is_online' => 'required|boolean',
            'status' => 'required|in:active,inactive,completed',
            'cover_image' => 'nullable|string|max:500',
            'cover_image_file' => 'nullable|image|max:5120',
        ]);
        $validated['available_seats'] = $validated['available_seats'] ?? $validated['total_seats'];
        if ($request->hasFile('cover_image_file')) {
            $validated['cover_image'] = $this->uploadCoverImage($request->file('cover_image_file'));
        }
        unset($validated['cover_image_file']);
        Event::create($validated);
        return redirect()->route('admin.events.index')->with('success', 'Event created.');
    }

    public function edit(Event $event)
    {
        $categories = EventCategory::orderBy('name')->get();
        return view('admin.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date' => 'sometimes|date',
            'time' => 'sometimes|string|max:20',
            'total_seats' => 'sometimes|integer|min:1',
            'available_seats' => 'sometimes|integer|min:0',
            'coins' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:event_categories,id',
            'is_online' => 'sometimes|boolean',
            'status' => 'sometimes|in:active,inactive,completed',
            'cover_image' => 'nullable|string|max:500',
            'cover_image_file' => 'nullable|image|max:5120',
        ]);
        if ($request->hasFile('cover_image_file')) {
            $validated['cover_image'] = $this->uploadCoverImage($request->file('cover_image_file'));
        }
        unset($validated['cover_image_file']);
        $soldCount = $event->bookings()->count();
        if (isset($validated['total_seats']) && $validated['total_seats'] < $soldCount) {
            return back()->withErrors(['total_seats' => 'Total seats cannot be less than already sold (' . $soldCount . ').']);
        }
        if (isset($validated['available_seats'])) {
            $minAvailable = ($validated['total_seats'] ?? $event->total_seats) - $soldCount;
            if ($validated['available_seats'] < $minAvailable) {
                return back()->withErrors(['available_seats' => 'Available seats cannot be less than ' . $minAvailable . ' (sold: ' . $soldCount . ').']);
            }
        }
        $event->update($validated);
        return redirect()->route('admin.events.index')->with('success', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        if ($event->bookings()->exists()) {
            return redirect()->route('admin.events.index')->with('error', 'Cannot delete event with bookings.');
        }
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        } else {
            $ids = array_filter((array) ($ids ?? []));
        }
        if (empty($ids)) {
            return redirect()->route('admin.events.index')->with('error', 'No events selected.');
        }
        $deleted = 0;
        foreach (Event::whereIn('id', $ids)->get() as $event) {
            if (!$event->bookings()->exists()) {
                $event->delete();
                $deleted++;
            }
        }
        return redirect()->route('admin.events.index')->with('success', $deleted ? "{$deleted} event(s) deleted." : 'No events deleted (events with bookings were skipped).');
    }

    private function uploadCoverImage(\Illuminate\Http\UploadedFile $file): string
    {
        if (CloudinaryService::isConfigured()) {
            $url = app(CloudinaryService::class)->uploadImage($file);
            if ($url) {
                return $url;
            }
        }
        $path = $file->store('events', 'public');
        return Storage::disk('public')->url($path);
    }
}
