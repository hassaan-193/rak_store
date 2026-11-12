<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatchFieldsToPortalOrders extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('portal_orders', 'site_name')) {
            return;
        }
        Schema::table('portal_orders', function (Blueprint $table) {
            // Where to deliver, as confirmed by the portal user on "Dispatch".
            $table->string('site_name')->nullable()->after('project_id');
            $table->text('delivery_address')->nullable()->after('site_name');
            $table->string('site_contact_name')->nullable()->after('delivery_address');
            $table->string('site_contact_phone', 50)->nullable()->after('site_contact_name');
            $table->text('dispatch_notes')->nullable()->after('site_contact_phone');
            $table->string('requested_by')->nullable()->after('dispatch_notes');
        });
    }

    public function down()
    {
        Schema::table('portal_orders', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'delivery_address', 'site_contact_name', 'site_contact_phone', 'dispatch_notes', 'requested_by']);
        });
    }
}
