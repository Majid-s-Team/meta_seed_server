<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLivestreamRequest;
use App\Http\Requests\UpdateLivestreamRequest;
use App\Models\Livestream;
use App\Services\AgoraRtlsService;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;
use Illuminate\Http\Request;

/**
 * Admin livestream management: schedule, edit, go live, end stream, view participants.
 */
class LivestreamController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all livestreams (any status).
     */
    public function index(Request $request)
    {
        try {
            $query = Livestream::with('creator:id,name,email');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $livestreams = $query->orderBy('scheduled_at', 'desc')->get();

            return $this->successResponse('SUCCESS', $livestreams);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Schedule a new livestream.
     */
    public function store(StoreLivestreamRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();
            $data['status'] = 'scheduled';
            $data['broadcast_type'] = $data['broadcast_type'] ?? Livestream::BROADCAST_TYPE_AGORA_RTC;

            if (($data['broadcast_type'] ?? '') === Livestream::BROADCAST_TYPE_RTMP) {
                $channel = $data['agora_channel'];
                if (empty($data['rtmp_url'] ?? null)) {
                    $data['rtmp_url'] = Livestream::defaultRtmpUrlForChannel($channel);
                }
                if (empty($data['rtmp_stream_key'] ?? null)) {
                    try {
                        $data['rtmp_stream_key'] = app(AgoraRtlsService::class)->createStreamKey($channel, '1', 0);
                    } catch (\Throwable $e) {
                        return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Stream key creation failed: ' . $e->getMessage());
                    }
                }
            }

            $livestream = Livestream::create($data);

            return $this->apiResponse(ResponseCode::CREATED, 'SUCCESS', $livestream);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Update a scheduled livestream (only scheduled can be edited; business rule).
     */
    public function update(UpdateLivestreamRequest $request, $id)
    {
        try {
            $livestream = Livestream::findOrFail($id);

            if ($livestream->status !== 'scheduled') {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Only scheduled streams can be edited');
            }

            $data = $request->validated();
            if (isset($data['broadcast_type']) && $data['broadcast_type'] === Livestream::BROADCAST_TYPE_RTMP) {
                $channel = $data['agora_channel'] ?? $livestream->agora_channel;
                if (empty($data['rtmp_url'] ?? null)) {
                    $data['rtmp_url'] = Livestream::defaultRtmpUrlForChannel($channel);
                }
                if (empty($data['rtmp_stream_key'] ?? null)) {
                    try {
                        $data['rtmp_stream_key'] = app(AgoraRtlsService::class)->createStreamKey($channel, (string) $livestream->id, 0);
                    } catch (\Throwable $e) {
                        return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Stream key creation failed: ' . $e->getMessage());
                    }
                }
            }
            $livestream->update($data);

            return $this->successResponse('SUCCESS', $livestream->fresh());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Set stream status to LIVE. Only scheduled streams can go live.
     */
    public function goLive($id)
    {
        try {
            $livestream = Livestream::findOrFail($id);

            if ($livestream->status !== 'scheduled') {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Only scheduled streams can go live');
            }

            $livestream->update([
                'status' => 'live',
                'stream_started_at' => now(),
                'stream_health_status' => Livestream::STREAM_HEALTH_STATUS_WAITING,
            ]);

            return $this->successResponse('SUCCESS', $livestream->fresh());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * End the stream. Sets status to ended, ended_at, current_viewer_count = 0,
     * stream_health_status, stream_duration_seconds, revenue_earned (aligned with web admin).
     */
    public function endStream($id)
    {
        try {
            $livestream = Livestream::findOrFail($id);
            if ($livestream->status !== 'live') {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Stream is not live');
            }
            $started = $livestream->stream_started_at ?? $livestream->updated_at;
            $durationSeconds = $started ? (int) now()->diffInSeconds($started) : 0;
            $revenue = (float) $livestream->bookings()->sum('amount_paid');
            $endedAt = now();

            $livestream->update([
                'status' => 'ended',
                'ended_at' => $endedAt,
                'current_viewer_count' => 0,
                'stream_health_status' => Livestream::STREAM_HEALTH_STATUS_OFFLINE,
                'stream_duration_seconds' => $durationSeconds,
                'revenue_earned' => $revenue,
            ]);
            $expiresAt = $endedAt->copy()->addHours(config('services.livestream.access_expiry_hours', 24));
            $livestream->bookings()->whereNull('access_expires_at')->update(['access_expires_at' => $expiresAt]);

            return $this->successResponse('SUCCESS', $livestream->fresh());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * List participants (users who booked this livestream).
     */
    public function participants($id)
    {
        try {
            $livestream = Livestream::findOrFail($id);
            $participants = $livestream->bookings()->with('user:id,name,email')->get()->map(function ($booking) {
                return [
                    'user_id' => $booking->user->id,
                    'name' => $booking->user->name,
                    'email' => $booking->user->email,
                    'booked_at' => $booking->booked_at->toDateTimeString(),
                ];
            });

            return $this->successResponse('SUCCESS', [
                'livestream_id' => $livestream->id,
                'title' => $livestream->title,
                'participants' => $participants,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }
}
