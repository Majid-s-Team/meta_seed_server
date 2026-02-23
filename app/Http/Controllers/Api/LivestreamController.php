<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livestream;
use App\Models\LivestreamBooking;
use App\Models\Transaction;
use App\Models\Wallet;
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
     * Paid streams (price > 0): deduct coins from wallet, record transaction, set amount_paid on booking.
     * Free streams: no charge. Access expiration can be set later (e.g. when stream ends).
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

            $amountPaid = 0;
            $priceCoins = (int) round((float) $livestream->price);

            if ($priceCoins > 0) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0]
                );
                if ($wallet->balance < $priceCoins) {
                    return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Insufficient coins to book this stream');
                }
                $wallet->decrement('balance', $priceCoins);
                $amountPaid = (float) $livestream->price;
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $priceCoins,
                    'description' => 'Livestream booking: ' . $livestream->title,
                    'reference_type' => 'livestream_booking',
                    'reference_id' => null, // set after booking created
                ]);
            }

            $booking = LivestreamBooking::create([
                'user_id' => $user->id,
                'livestream_id' => $livestream->id,
                'booked_at' => now(),
                'amount_paid' => $amountPaid,
                'access_expires_at' => null, // optional: set when stream ends for time-limited replay
            ]);

            if ($priceCoins > 0) {
                Transaction::where('user_id', $user->id)
                    ->where('reference_type', 'livestream_booking')
                    ->whereNull('reference_id')
                    ->latest('id')
                    ->first()
                    ?->update(['reference_id' => $booking->id]);
            }

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
                // Access expiration: block join if booking access has expired (e.g. post-stream replay window ended)
                if ($booking->isAccessExpired()) {
                    return $this->errorResponse(ResponseCode::FORBIDDEN, 'Access to this stream has expired');
                }
            }

            $agoraService = app(AgoraService::class);
            $token = $agoraService->generateRtcToken($livestream->agora_channel, $user->id);
            $appId = $agoraService->getAppId();
            $uid = $agoraService->normalizeUid($user->id);

            return response()->json([
                'status_code' => ResponseCode::SUCCESS,
                'message' => 'SUCCESS',
                'data' => [
                    'app_id' => $appId,
                    'channel' => $livestream->agora_channel,
                    'token' => $token,
                    'uid' => $uid,
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
     * Analytics: client reports viewer joined. Increments current_viewer_count and total_viewer_count,
     * updates peak_viewer_count if current exceeds it. Optional; improves admin dashboard metrics.
     */
    public function viewerJoined($id)
    {
        try {
            $livestream = Livestream::findOrFail($id);
            if ($livestream->status !== 'live') {
                return $this->errorResponse(ResponseCode::BAD_REQUEST, 'Stream is not live');
            }
            $livestream->increment('current_viewer_count');
            $livestream->increment('total_viewer_count');
            $current = $livestream->fresh()->current_viewer_count;
            $peak = $livestream->peak_viewer_count ?? 0;
            if ($current > $peak) {
                $livestream->update(['peak_viewer_count' => $current]);
            }
            return $this->successResponse('SUCCESS', ['current_viewer_count' => $livestream->fresh()->current_viewer_count]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Analytics: client reports viewer left. Decrements current_viewer_count (floor 0).
     */
    public function viewerLeft($id)
    {
        try {
            $livestream = Livestream::findOrFail($id);
            if ($livestream->status === 'live' && $livestream->current_viewer_count > 0) {
                $livestream->decrement('current_viewer_count');
            }
            return $this->successResponse('SUCCESS', ['current_viewer_count' => $livestream->fresh()->current_viewer_count]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(ResponseCode::NOT_FOUND, 'Livestream not found');
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseCode::INTERNAL_SERVER_ERROR, 'SERVER_ERROR');
        }
    }

    /**
     * Local test only: list live streams without auth. Used when LIVESTREAM_LOCAL_TEST=true.
     */
    public function testLive(Request $request)
    {
        if (!config('services.livestream.local_test', false)) {
            return response()->json(['message' => 'Not available'], 404);
        }
        return $this->live($request);
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

            $payload = [
                'app_id' => $data['app_id'],
                'channel' => $data['channel'],
                'token' => $data['token'],
                'uid' => $data['uid'],
            ];
            // Debug: show whether server sent a token (helps diagnose config/cache issues)
            if (config('services.livestream.local_test', false)) {
                $payload['_token_status'] = isset($data['token']) && $data['token'] !== '' && $data['token'] !== null
                    ? 'present'
                    : 'missing (set AGORA_APP_CERTIFICATE and run php artisan config:clear on server)';
            }

            return response()->json([
                'status_code' => ResponseCode::SUCCESS,
                'message' => 'SUCCESS',
                'data' => $payload,
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
