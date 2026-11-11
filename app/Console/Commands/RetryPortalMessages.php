<?php

namespace App\Console\Commands;

use App\Services\Integration\PortalClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetryPortalMessages extends Command
{
    protected $signature = 'portal:retry';

    protected $description = 'Resend pending store-to-portal events that have not been delivered yet';

    public function handle()
    {
        $ids = DB::table('integration_messages')
            ->where('direction', 'out')->where('status', 'pending')
            ->where('next_attempt_at', '<=', now())
            ->orderBy('id')->limit(100)->pluck('id');

        $ok = 0;
        foreach ($ids as $id) {
            $ok += PortalClient::deliver($id) ? 1 : 0;
        }

        $this->info("Retried {$ids->count()} message(s), {$ok} delivered.");

        return 0;
    }
}
