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
     * POST /api/agora/webhook — Agora callback for RTMP stream start/stop.
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
            return response('', 401);
        }

        if (!$payload) {
            Log::channel('single')->info('Agora webhook: empty or invalid JSON (health check?)', ['ip' => $request->ip()]);
            return response('', 200);
        }

        $eventType = $this->normalizeEventType($payload);
        $channelName = $this->extractChannelName($payload);
        $sid = $payload['sid'] ?? $payload['sessionId'] ?? $payload['data']['sid'] ?? ('req_' . md5($rawBody . $request->ip()));

        if (!$channelName) {
            Log::channel('single')->info('Agora webhook: no channel in payload (health check or unknown event)', ['payload_keys' => array_keys($payload)]);
            return response('', 200);
        }

        $dedupeKey = self::DEDUPE_CACHE_PREFIX . $sid . '_' . $eventType;
        if (Cache::has($dedupeKey)) {
            Log::channel('single')->debug('Agora webhook: duplicate event ignored', ['sid' => $sid, 'event' => $eventType]);
            return response('', 200);
        }

        $livestream = Livestream::where('agora_channel', $channelName)->whereIn('status', ['scheduled', 'live'])->first();
        if (!$livestream) {
            Log::channel('single')->info('Agora webhook: no livestream for channel', ['channel' => $channelName]);
            return response('', 200);
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

        return response('', 200);
    }

    private function parsePayload(string $raw): ?array
    {
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeEventType(array $payload): ?string
    {
        $event = $payload['event'] ?? $payload['eventType'] ?? $payload['event_type'] ?? $payload['data']['event'] ?? null;
        if (!$event) {
            return null;
        }
        $e = strtolower((string) $event);
        if (in_array($e, ['live_stream_connected', 'rtmp_stream_started', 'stream_started', 'feed_detected'], true)) {
            return 'feed_detected';
        }
        if (in_array($e, ['live_stream_disconnected', 'rtmp_stream_stopped', 'stream_stopped', 'feed_stopped'], true)) {
            return 'feed_stopped';
        }
        return null;
    }

    private function extractChannelName(array $payload): ?string
    {
        $candidates = [
            $payload['rtcInfo']['channel'] ?? null,
            $payload['channel'] ?? null,
            $payload['data']['rtcInfo']['channel'] ?? null,
            $payload['data']['channel'] ?? null,
        ];
        foreach ($candidates as $c) {
            if ($c !== null && $c !== '') {
                return (string) $c;
            }
        }
        if (isset($payload['streamKey']) && is_string($payload['streamKey']) && $payload['streamKey'] !== '') {
            return $payload['streamKey'];
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

        $signature = $request->header('X-Agora-Signature') ?? $request->header('x-agora-signature') ?? '';
        if ($signature === '') {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature) || hash_equals('sha1=' . hash_hmac('sha1', $rawBody, $secret), $signature);
    }
}
