<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRecording;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventRecordingController extends Controller
{
    public function index()
    {
        $recordings = EventRecording::with('event')->orderBy('sort_order')->orderBy('recorded_at', 'desc')->paginate(15);
        return view('admin.recordings.index', compact('recordings'));
    }

    public function create()
    {
        $events = Event::orderBy('date', 'desc')->get();
        return view('admin.recordings.create', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimes:mp4,mov,webm|max:102400',
            'thumbnail_url' => 'nullable|url|max:500',
            'thumbnail_file' => 'nullable|image|max:5120',
            'recorded_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'boolean',
        ]);
        $validated['is_visible'] = $request->boolean('is_visible', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('video_file')) {
            $this->uploadRecordingVideo($request->file('video_file'), $validated);
        }
        if (empty($validated['video_path']) && empty($validated['video_url'])) {
            return back()->withErrors(['video_file' => 'Please upload a video file or provide a video URL.'])->withInput();
        }
        if ($request->hasFile('thumbnail_file')) {
            $validated['thumbnail_url'] = $this->uploadRecordingThumbnail($request->file('thumbnail_file')) ?? $validated['thumbnail_url'] ?? null;
        }
        unset($validated['video_file'], $validated['thumbnail_file']);
        EventRecording::create($validated);
        return redirect()->route('admin.recordings.index')->with('success', 'Recording added.');
    }

    public function edit(EventRecording $recording)
    {
        $events = Event::orderBy('date', 'desc')->get();
        return view('admin.recordings.edit', compact('recording', 'events'));
    }

    public function update(Request $request, EventRecording $recording)
    {
        $validated = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimes:mp4,mov,webm|max:102400',
            'thumbnail_url' => 'nullable|url|max:500',
            'thumbnail_file' => 'nullable|image|max:5120',
            'recorded_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'boolean',
        ]);
        $validated['is_visible'] = $request->boolean('is_visible', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $recording->sort_order);

        if ($request->hasFile('video_file')) {
            if ($recording->video_path) {
                Storage::disk('public')->delete($recording->video_path);
            }
            $validated['video_path'] = null;
            $validated['video_url'] = null;
            $this->uploadRecordingVideo($request->file('video_file'), $validated);
        }
        if ($request->hasFile('thumbnail_file')) {
            $validated['thumbnail_url'] = $this->uploadRecordingThumbnail($request->file('thumbnail_file')) ?? $validated['thumbnail_url'] ?? null;
        }
        unset($validated['video_file'], $validated['thumbnail_file']);
        $recording->update($validated);
        return redirect()->route('admin.recordings.index')->with('success', 'Recording updated.');
    }

    public function destroy(EventRecording $recording)
    {
        if ($recording->video_path) {
            Storage::disk('public')->delete($recording->video_path);
        }
        $recording->delete();
        return redirect()->route('admin.recordings.index')->with('success', 'Recording deleted.');
    }

    private function uploadRecordingVideo(\Illuminate\Http\UploadedFile $file, array &$validated): void
    {
        if (CloudinaryService::isConfigured()) {
            $url = app(CloudinaryService::class)->uploadVideo($file);
            if ($url) {
                $validated['video_url'] = $url;
                $validated['video_path'] = null;
                return;
            }
        }
        $validated['video_path'] = $file->store('recordings', 'public');
        $validated['video_url'] = null;
    }

    private function uploadRecordingThumbnail(\Illuminate\Http\UploadedFile $file): ?string
    {
        if (CloudinaryService::isConfigured()) {
            return app(CloudinaryService::class)->uploadThumbnail($file);
        }
        $path = $file->store('recordings/thumbnails', 'public');
        return Storage::disk('public')->url($path);
    }
}
