<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livestream;
use App\Services\BroadcastWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agora webhook endpoint for RTMP stream lifecycle.
 *
 * Detection flow:
 * 1. Agora sends POST with JSON body (e.g. live_stream_connected / live_stream_disconnected).
 * 2. We validate signature (if AGORA_WEBHOOK_SECRET set) and optional IP allowlist.
 * 3. We dedupe by sid+event so duplicate/retry webhooks don't flap status.
 * 4. We resolve livestream by channel name (rtcInfo.channel or channel).
 * 5. We call BroadcastWorkflowService::reportRtmpFeedDetected or reportRtmpFeedStopped.
 * 6. Manual override (status_manual_override) is respected; no auto transition then.
 *
 * Supported event types (Agora Media Gateway / RTMP ingest):
 * - live_stream_connected, rtmp_stream_started → feed detected
 * - live_stream_disconnected, rtmp_stream_stopped → feed stopped
 */
class AgoraWebhookController extends Controller
{
    private const DEDUPE_TTL_SECONDS = 300; // 5 min
    private const DEDUPE_CACHE_PREFIX = 'agora_webhook_';

    public function __construct(
        private BroadcastWorkflowService $workflow
    ) {}

    /**
     * POST /api/agora/webhook — Agora Media Gateway callback for RTMP stream lifecycle.
     * Agora sends: { "noticeId", "productId", "eventType": 1|2, "notifyMs", "sid", "payload": { "rtcInfo": { "channel", "uid" }, "streamKey", ... } }.
     * eventType 1 = live_stream_connected, 2 = live_stream_disconnected.
     */
    public function __invoke(Request $request): Response
    {
        $rawBody = $request->getContent();
        $payload = $this->parsePayload($rawBody);

        if (!$this->validateRequest($request, $rawBody)) {
            Log::channel('single')->warning('Agora webhook: invalid request', [
                'ip' => $request->ip(),
                'reason' => 'signature_or_ip_failed',
            ]);
            return response()->json([], 401);
        }

        if (!$payload) {
            Log::channel('single')->info('Agora webhook: empty or invalid JSON (health check?)', ['ip' => $request->ip()]);
            return response()->json(['status' => 'ok'], 200);
        }

        // Agora wraps the event payload in a top-level "payload" key; use it for channel/streamKey
        $body = $payload['payload'] ?? $payload;
        $eventType = $this->normalizeEventType($payload, $body);
        $channelName = $this->extractChannelName($body);
        $streamKeyFromPayload = $body['streamKey'] ?? null;
        $sid = $payload['sid'] ?? $body['sid'] ?? $payload['sessionId'] ?? ('req_' . md5($rawBody . $request->ip()));

        Log::channel('single')->info('Agora webhook: received', [
            'eventType' => $eventType,
            'channel' => $channelName,
            'streamKey_present' => !empty($streamKeyFromPayload),
            'sid' => $sid,
        ]);

        if (!$channelName && !$streamKeyFromPayload) {
            Log::channel('single')->info('Agora webhook: no channel or streamKey in payload', ['payload_keys' => array_keys($payload), 'body_keys' => array_keys($body)]);
            return response()->json(['status' => 'ok'], 200);
        }

        $dedupeKey = self::DEDUPE_CACHE_PREFIX . $sid . '_' . ($eventType ?? 'unknown');
        if (Cache::has($dedupeKey)) {
            Log::channel('single')->debug('Agora webhook: duplicate event ignored', ['sid' => $sid, 'event' => $eventType]);
            return response()->json(['status' => 'ok'], 200);
        }

        $livestream = $this->findLivestream($channelName, $streamKeyFromPayload);
        if (!$livestream) {
            Log::channel('single')->info('Agora webhook: no livestream for channel/streamKey', [
                'channel' => $channelName,
                'stream_key_prefix' => $streamKeyFromPayload ? substr($streamKeyFromPayload, 0, 8) . '...' : null,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $metadata = [
            'sid' => $sid,
            'channel' => $channelName,
            'event' => $eventType,
            'payload_keys' => array_keys($payload),
        ];

        if ($eventType === 'feed_detected') {
            Cache::put($dedupeKey, true, self::DEDUPE_TTL_SECONDS);
            $this->workflow->reportRtmpFeedDetected($livestream, $metadata);
        } elseif ($eventType === 'feed_stopped') {
            Cache::put($dedupeKey, true, self::DEDUPE_TTL_SECONDS);
            $this->workflow->reportRtmpFeedStopped($livestream, $metadata);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function findLivestream(?string $channelName, ?string $streamKey): ?Livestream
    {
        $query = Livestream::whereIn('status', ['scheduled', 'live']);
        if ($channelName !== null && $channelName !== '') {
            $byChannel = (clone $query)->where('agora_channel', $channelName)->first();
            if ($byChannel) {
                return $byChannel;
            }
        }
        if ($streamKey !== null && $streamKey !== '') {
            return $query->where('rtmp_stream_key', $streamKey)->first();
        }
        return null;
    }

    private function parsePayload(string $raw): ?array
    {
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeEventType(array $payload, array $body): ?string
    {
        $eventType = $payload['eventType'] ?? $payload['event'] ?? $body['eventType'] ?? $body['event'] ?? $payload['event_type'] ?? $body['event_type'] ?? null;
        if ($eventType !== null) {
            if (is_numeric($eventType)) {
                if ((int) $eventType === 1) {
                    return 'feed_detected';
                }
                if ((int) $eventType === 2) {
                    return 'feed_stopped';
                }
            }
            $e = strtolower((string) $eventType);
            if (in_array($e, ['live_stream_connected', 'rtmp_stream_started', 'stream_started', 'feed_detected'], true)) {
                return 'feed_detected';
            }
            if (in_array($e, ['live_stream_disconnected', 'rtmp_stream_stopped', 'stream_stopped', 'feed_stopped'], true)) {
                return 'feed_stopped';
            }
        }
        return null;
    }

    private function extractChannelName(array $body): ?string
    {
        $candidates = [
            $body['rtcInfo']['channel'] ?? null,
            $body['channel'] ?? null,
            $body['channelName'] ?? null,
        ];
        foreach ($candidates as $c) {
            if ($c !== null && $c !== '') {
                return (string) $c;
            }
        }
        if (isset($body['streamKey']) && is_string($body['streamKey']) && $body['streamKey'] !== '') {
            return $body['streamKey'];
        }
        return null;
    }

    private function validateRequest(Request $request, string $rawBody): bool
    {
        $allowedIps = config('services.agora.webhook_allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps, true)) {
            return false;
        }

        $secret = config('services.agora.webhook_secret', '');
        if ($secret === '') {
            return true;
        }

        $signature = $request->header('X-Agora-Signature')
            ?? $request->header('x-agora-signature')
            ?? $request->header('Agora-Signature')
            ?? $request->header('Agora-Signature-V2')
            ?? '';
        if ($signature === '') {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature) || hash_equals('sha1=' . hash_hmac('sha1', $rawBody, $secret), $signature);
    }
}
