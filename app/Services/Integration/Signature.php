<?php

namespace App\Services\Integration;

use Illuminate\Support\Str;

/**
 * HMAC-SHA256 request signing shared by the store and the FTS portal.
 * Keep this identical to App\Services\StoreIntegration\Signature in the portal.
 */
class Signature
{
    public static function make(string $secret, string $timestamp, string $messageId, string $method, string $path, string $body): string
    {
        $base = implode("\n", [$timestamp, $messageId, strtoupper($method), '/' . ltrim($path, '/'), hash('sha256', $body)]);

        return hash_hmac('sha256', $base, $secret);
    }

    public static function headers(string $keyId, string $secret, string $method, string $path, string $body, ?string $messageId = null): array
    {
        $timestamp = (string) time();
        $messageId = $messageId ?: (string) Str::uuid();

        return [
            'X-FTS-Key' => $keyId,
            'X-FTS-Timestamp' => $timestamp,
            'X-FTS-Message-Id' => $messageId,
            'X-FTS-Signature' => self::make($secret, $timestamp, $messageId, $method, $path, $body),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
