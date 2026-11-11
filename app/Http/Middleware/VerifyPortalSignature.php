<?php

namespace App\Http\Middleware;

use App\Services\Integration\Signature;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyPortalSignature
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('portal.enabled') || !config('portal.inbound_secret')) {
            return response()->json(['message' => 'Integration disabled'], 503);
        }

        $allowed = config('portal.allowed_ips');
        if (!empty($allowed) && !in_array($request->ip(), $allowed, true)) {
            return response()->json(['message' => 'IP not allowed'], 403);
        }

        $keyId = (string) $request->header('X-FTS-Key');
        $timestamp = (string) $request->header('X-FTS-Timestamp');
        $messageId = (string) $request->header('X-FTS-Message-Id');
        $signature = (string) $request->header('X-FTS-Signature');

        if ($keyId !== config('portal.inbound_key_id') || !ctype_digit($timestamp) || $messageId === '' || strlen($messageId) > 64 || $signature === '') {
            return response()->json(['message' => 'Missing or invalid signature headers'], 401);
        }

        if (abs(time() - (int) $timestamp) > (int) config('portal.max_clock_skew')) {
            return response()->json(['message' => 'Request expired'], 401);
        }

        $expected = Signature::make(config('portal.inbound_secret'), $timestamp, $messageId, $request->method(), $request->path(), $request->getContent());
        if (!hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Bad signature'], 401);
        }

        // Replay protection: each message id is processed once (GETs are read-only, so they are skipped).
        if (!$request->isMethod('get')) {
            $seen = DB::table('integration_messages')->where('direction', 'in')->where('message_id', $messageId)->first();
            if ($seen) {
                return response()->json((json_decode($seen->response ?: '{}', true) ?: []) + ['duplicate' => true]);
            }
        }

        $response = $next($request);

        if (!$request->isMethod('get') && $response->getStatusCode() < 500) {
            DB::table('integration_messages')->insert([
                'direction' => 'in',
                'message_id' => $messageId,
                'event' => $request->path(),
                'payload' => $request->getContent(),
                'response' => $response->getContent(),
                'status' => $response->getStatusCode() < 300 ? 'done' : 'rejected',
                'attempts' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $response;
    }
}
