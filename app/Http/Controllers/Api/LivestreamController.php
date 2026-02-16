<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livestream;
use App\Models\LivestreamBooking;
use App\Services\AgoraService;
use App\Traits\ApiResponseTrait;
use App\Constants\ResponseCode;
use Illuminate\Http\Request;

/**
 * User-facing livestream: view upcoming, show, book, join (get Agora token).
 */
class LivestreamController extends Controller
{
    use ApiResponseTrait;

    /**
     * List upcoming (scheduled) livestreams.
     */
    public function upcoming(Request $request)
    {
        try {
            $query = Livestream::where('status', 'scheduled')
                ->where('scheduled_at', '>', now())
                ->orderBy('scheduled_at', 'asc');

            $livestreams = $query->get();

            if (auth()->check()) {
                $bookedIds = LivestreamBooking::where('user_id', auth()->id())->pluck('livestream_id')->toArray();
                $livestreams->transform(function ($stream) use ($bookedIds) {
                    $stream->isBooked = in_array($stream->id, $bookedIds);
                    return $stream;
                });
            }

            return $this->successResponse('SUCCESS', $livestreams);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Show a single livestream (upcoming or live).
     */
    public function show($id)
    {
        try {
            $livestream = Livestream::find($id);

            if (!$livestream) {
                return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
            }

            $livestream->isBooked = false;
            if (auth()->check()) {
                $livestream->isBooked = LivestreamBooking::where('user_id', auth()->id())
                    ->where('livestream_id', $livestream->id)
                    ->exists();
            }

            return $this->successResponse('SUCCESS', $livestream);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Book a livestream (user gets a slot; required to join when live).
     */
    public function book($id)
    {
        try {
            $livestream = Livestream::findOrFail($id);
            $user = auth()->user();

            if ($livestream->status !== 'scheduled') {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Only upcoming streams can be booked');
            }

            $alreadyBooked = LivestreamBooking::where('user_id', $user->id)
                ->where('livestream_id', $livestream->id)
                ->exists();

            if ($alreadyBooked) {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'You have already booked this stream');
            }

            $currentBookings = LivestreamBooking::where('livestream_id', $livestream->id)->count();
            if ($currentBookings >= $livestream->max_participants) {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Max participants reached');
            }

            LivestreamBooking::create([
                'user_id' => $user->id,
                'livestream_id' => $livestream->id,
                'booked_at' => now(),
            ]);

            return $this->successResponse('Stream booked successfully', ['message' => 'Stream booked successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * List streams that are currently LIVE (for test page / clients).
     */
    public function live(Request $request)
    {
        try {
            $livestreams = Livestream::where('status', 'live')->orderBy('scheduled_at', 'desc')->get();
            return $this->successResponse('SUCCESS', $livestreams);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Join a LIVE stream: verify booking (or skip in test mode), return Agora app_id, channel, token.
     * Add ?test=1 to skip booking check (for web testing interface).
     */
    public function join(Request $request, $id)
    {
        try {
            $livestream = Livestream::findOrFail($id);
            $user = auth()->user();
            $testMode = $request->boolean('test');

            if ($livestream->status !== 'live') {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Stream is not live');
            }

            if (!$testMode) {
                $booking = LivestreamBooking::where('user_id', $user->id)
                    ->where('livestream_id', $livestream->id)
                    ->first();
                if (!$booking) {
                    return $this->errorResponse(ResponseCode::FORBIDDEN, 'You must book this stream to join');
                }
            }

            $agoraService = app(AgoraService::class);
            $token = $agoraService->generateRtcToken($livestream->agora_channel, $user->id);
            $appId = $agoraService->getAppId();

            return response()->json([
                'status_code' => ResponseCode::SUCCESS,
                'message' => 'SUCCESS',
                'data' => [
                    'app_id' => $appId,
                    'channel' => $livestream->agora_channel,
                    'token' => $token,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse(ResponseCode::BAD_REQUEST, $e->getMessage());
        } catch (\RuntimeException $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Local test only: list live streams without auth. Used when LIVESTREAM_LOCAL_TEST=true.
     */
    public function testLive()
    {
        if (!config('services.livestream.local_test', false)) {
            return response()->json(['message' => 'Not available'], 404);
        }
        return $this->live();
    }

    /**
     * Local test only: return Agora credentials without auth. Used when LIVESTREAM_LOCAL_TEST=true.
     * Stream must be live. Returns app_id, channel, token (null if no certificate for testing with token disabled).
     */
    public function testCredentials($id)
    {
        if (!config('services.livestream.local_test', false)) {
            return response()->json(['message' => 'Not available'], 404);
        }

        try {
            $livestream = Livestream::findOrFail($id);
            if ($livestream->status !== 'live') {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Stream is not live. Set it LIVE on the admin page first.');
            }

            $agoraService = app(AgoraService::class);
            $data = $agoraService->getCredentialsForChannel($livestream->agora_channel, 1);

            return response()->json([
                'status_code' => ResponseCode::SUCCESS,
                'message' => 'SUCCESS',
                'data' => [
                    'app_id' => $data['app_id'],
                    'channel' => $data['channel'],
                    'token' => $data['token'],
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse(ResponseCode::BAD_REQUEST, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
}
