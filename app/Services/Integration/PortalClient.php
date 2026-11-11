<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Sends signed events to the FTS portal. Every event is saved in integration_messages
 * first (outbox), so if the portal is down it is retried by `php artisan portal:retry`.
 */
class PortalClient
{
    public const EVENTS_PATH = 'api/store/events';

    public static function queueEvent(string $event, array $data): void
    {
        $id = self::enqueue($event, $data);
        if ($id) {
            self::deliver($id);
        }
    }

    /** Save the event in the outbox only (safe inside a DB transaction); deliver() it after commit. */
    public static function enqueue(string $event, array $data): ?int
    {
        if (!config('portal.enabled')) {
            return null;
        }

        $id = DB::table('integration_messages')->insertGetId([
            'direction' => 'out',
            'message_id' => (string) Str::uuid(),
            'event' => $event,
            'payload' => json_encode(['event' => $event, 'data' => $data, 'sent_at' => now()->toIso8601String()]),
            'status' => 'pending',
            'attempts' => 0,
            'next_attempt_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    /** Try to deliver one outbox message; returns true when the portal accepted it. */
    public static function deliver(int $id): bool
    {
        $msg = DB::table('integration_messages')->find($id);
        if (!$msg || $msg->status !== 'pending') {
            return false;
        }

        $attempts = $msg->attempts + 1;
        $error = null;

        try {
            $headers = Signature::headers(config('portal.outbound_key_id'), (string) config('portal.outbound_secret'), 'POST', self::EVENTS_PATH, $msg->payload, $msg->message_id);
            $response = Http::withHeaders($headers)
                ->timeout(config('portal.timeout'))
                ->withBody($msg->payload, 'application/json')
                ->post(config('portal.url') . '/' . self::EVENTS_PATH);

            if ($response->successful()) {
                DB::table('integration_messages')->where('id', $id)->update([
                    'status' => 'done', 'attempts' => $attempts, 'response' => $response->body(), 'last_error' => null, 'updated_at' => now(),
                ]);

                return true;
            }
            $error = 'HTTP ' . $response->status() . ': ' . Str::limit($response->body(), 500);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        Log::warning('Portal event delivery failed', ['message_id' => $msg->message_id, 'error' => $error]);

        DB::table('integration_messages')->where('id', $id)->update([
            'status' => $attempts >= config('portal.max_attempts') ? 'failed' : 'pending',
            'attempts' => $attempts,
            'last_error' => $error,
            // back off: 1, 2, 4, 8 ... minutes, max 60
            'next_attempt_at' => now()->addMinutes(min(60, 2 ** ($attempts - 1))),
            'updated_at' => now(),
        ]);

        return false;
    }
}
