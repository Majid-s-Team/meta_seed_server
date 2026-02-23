<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

/**
 * Generates Agora RTC tokens securely for livestream join.
 * Uses AGORA_APP_ID and AGORA_APP_CERTIFICATE from env (via config/services.php).
 * Requires: composer require taylanunutmaz/agora-token-builder
 */
class AgoraService
{
    /** Token validity: 2 hours in seconds */
    public const TOKEN_EXPIRY_SECONDS = 7200;

    /**
     * Generate an RTC token for a user to join a channel.
     *
     * @param string $channelName Agora channel name (e.g. livestream agora_channel value)
     * @param int|string $userId User identifier (Laravel user id; must be 1 to 2^32-1 for int uid)
     * @return string RTC token
     * @throws InvalidArgumentException when config is missing
     * @throws RuntimeException when token generation fails (e.g. package not installed)
     */
    public function generateRtcToken(string $channelName, $userId): string
    {
        $appId = $this->getAppId();
        $appCertificate = trim((string) config('services.agora.app_certificate', ''));

        if (empty($appCertificate)) {
            throw new InvalidArgumentException('AGORA_APP_CERTIFICATE is not set in .env');
        }

        if (!class_exists(\TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder::class)) {
            throw new RuntimeException(
                'Agora token builder not installed. Run: composer require taylanunutmaz/agora-token-builder'
            );
        }

        // Expiry: current Unix timestamp + 2 hours (Agora expects seconds since 1/1/1970)
        $privilegeExpiredTs = time() + self::TOKEN_EXPIRY_SECONDS;

        $uid = $this->normalizeUid($userId);

        $role = \TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder::RolePublisher;

        return \TaylanUnutmaz\AgoraTokenBuilder\RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );
    }

    /**
     * Get App ID from config (for client-side SDK init).
     */
    public function getAppId(): string
    {
        $appId = trim((string) config('services.agora.app_id', ''));
        if ($appId === '') {
            throw new InvalidArgumentException('AGORA_APP_ID is not set in .env');
        }
        return $appId;
    }

    /**
     * Normalize user id to Agora UID (1 to 2^32-1). Client must join with this same UID when using a token.
     */
    public function normalizeUid($userId): int
    {
        $uid = is_numeric($userId) ? (int) $userId : 0;
        if ($uid < 1 || $uid > 4294967295) {
            $uid = crc32((string) $userId);
            $uid = ($uid < 0) ? $uid + 4294967296 : $uid;
            if ($uid === 0) {
                $uid = 1;
            }
        }
        return $uid;
    }

    /**
     * Get credentials for local testing. When certificate is empty, token is null (Agora project must allow optional token).
     * Returns uid so the client can join with the same UID the token was generated for (required for token auth).
     */
    public function getCredentialsForChannel(string $channelName, $userId): array
    {
        $appId = $this->getAppId();
        $cert = trim((string) config('services.agora.app_certificate', ''));
        $uid = $this->normalizeUid($userId);
        $token = null;
        if ($cert !== '') {
            $token = $this->generateRtcToken($channelName, $userId);
        }
        return ['app_id' => $appId, 'channel' => $channelName, 'token' => $token, 'uid' => $uid];
    }
}
