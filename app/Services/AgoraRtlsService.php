<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * Agora RTLS (Real-Time Live Streaming) Ingress API.
 * Creates streaming keys for OBS/RTMP push to Agora (e.g. rtmp://rtls-ingress-prod-ap.agoramdn.com/live).
 * Authentication: use Customer ID + Customer Secret from Agora Console → Developer Toolkit → RESTful API.
 *
 * @see https://docs.agora.io/en/media-gateway/reference/rest-api/endpoints/streaming-key/create-streaming-key
 * @see https://docs.agora.io/en/media-gateway/reference/restful-authentication
 */
class AgoraRtlsService
{
    /**
     * Create a streaming key for the given channel.
     * Uses POST .../rtls/ingress/streamkeys with Basic auth (Customer ID : Customer Secret).
     *
     * @param string $channel Agora channel name (e.g. livestream agora_channel), max 64 bytes
     * @param int|string|null $uid Host UID in channel; use 0 or null for random UID
     * @param int $expiresAfter Validity in seconds from creation; 0 = never expire
     * @return string The created stream key for use in OBS
     * @throws InvalidArgumentException when config is missing
     * @throws RuntimeException when API request fails
     */
    public function createStreamKey(string $channel, $uid = null, int $expiresAfter = 0): string
    {
        $appId = config('services.agora.app_id', '');
        if ($appId === '') {
            throw new InvalidArgumentException('AGORA_APP_ID must be set for RTLS stream key creation.');
        }

        $customerId = config('services.agora.rtls_customer_id', '');
        $customerSecret = config('services.agora.rtls_customer_secret', '');
        if ($customerId === '' || $customerSecret === '') {
            throw new InvalidArgumentException(
                'AGORA_RTLS_CUSTOMER_ID and AGORA_RTLS_CUSTOMER_SECRET must be set. Get them from Agora Console → Developer Toolkit → RESTful API (Add a secret, then download key_and_secret.txt).'
            );
        }

        $base = rtrim(config('services.agora.rtls_api_base', 'https://api.sd-rtn.com'), '/');
        $region = config('services.agora.rtls_region', 'ap');
        $url = "{$base}/{$region}/v1/projects/{$appId}/rtls/ingress/streamkeys";

        $credentials = base64_encode("{$customerId}:{$customerSecret}");
        $requestId = \Illuminate\Support\Str::uuid()->toString();

        // Channel and uid cannot both be empty/0 per Agora docs; use "1" if uid not provided
        $uidValue = $uid !== null && $uid !== '' ? (string) $uid : '1';

        $body = [
            'settings' => [
                'channel' => $channel,
                'uid' => $uidValue,
                'expiresAfter' => $expiresAfter,
            ],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $credentials,
            'X-Request-ID' => $requestId,
        ])->post($url, $body);

        if (!$response->successful()) {
            Log::warning('Agora RTLS createStreamKey failed', [
                'channel' => $channel,
                'status' => $response->status(),
                'body' => $response->body(),
                'request_id' => $requestId,
            ]);
            throw new RuntimeException(
                'Agora RTLS stream key creation failed: ' . $response->status() . ' ' . $response->body()
            );
        }

        $json = $response->json();
        $streamKey = $json['data']['streamKey'] ?? null;
        if ($streamKey === null || $streamKey === '') {
            throw new RuntimeException('Agora RTLS response did not contain data.streamKey.');
        }

        return $streamKey;
    }

    /**
     * Default RTMP server URL for OBS (RTLS Ingress).
     * Use this as the "Server" in OBS; stream key goes in "Stream Key" field.
     */
    public static function defaultRtmpServerUrl(): string
    {
        return config('services.agora.rtls_rtmp_url', 'rtmp://rtls-ingress-prod-ap.agoramdn.com/live');
    }
}
