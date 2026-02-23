<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventRecordingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EventRecording::where('is_visible', true)
            ->with('event:id,title,date')
            ->orderBy('sort_order')
            ->orderBy('recorded_at', 'desc');
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        $recordings = $query->paginate($request->input('per_page', 15));
        $items = $recordings->getCollection()->map(function (EventRecording $r) {
            return [
                'id' => $r->id,
                'event_id' => $r->event_id,
                'event_title' => $r->event?->title,
                'title' => $r->title,
                'description' => $r->description,
                'video_url' => $r->playable_url,
                'thumbnail_url' => $r->thumbnail_url,
                'recorded_at' => $r->recorded_at?->format('Y-m-d'),
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $recordings->currentPage(),
                'last_page' => $recordings->lastPage(),
                'per_page' => $recordings->perPage(),
                'total' => $recordings->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $recording = EventRecording::where('is_visible', true)->with('event')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $recording->id,
                'event_id' => $recording->event_id,
                'event_title' => $recording->event?->title,
                'title' => $recording->title,
                'description' => $recording->description,
                'video_url' => $recording->playable_url,
                'thumbnail_url' => $recording->thumbnail_url,
                'recorded_at' => $recording->recorded_at?->format('Y-m-d'),
            ],
        ]);
    }
}
