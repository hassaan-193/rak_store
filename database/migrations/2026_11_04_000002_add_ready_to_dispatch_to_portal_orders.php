<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReadyToDispatchToPortalOrders extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('portal_orders', 'ready_at')) {
            return;
        }
        Schema::table('portal_orders', function (Blueprint $table) {
            // Set by a store user on an "awaiting dispatch" order: material is ready, the portal may dispatch.
            $table->timestamp('ready_at')->nullable()->after('requested_by');
            $table->unsignedBigInteger('ready_by')->nullable()->after('ready_at');
        });
    }

    public function down()
    {
        Schema::table('portal_orders', function (Blueprint $table) {
            $table->dropColumn(['ready_at', 'ready_by']);
        });
    }
}
