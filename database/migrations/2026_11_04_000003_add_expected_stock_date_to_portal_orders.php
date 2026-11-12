<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpectedStockDateToPortalOrders extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('portal_orders', 'expected_stock_date')) {
            return;
        }
        Schema::table('portal_orders', function (Blueprint $table) {
            // Set by the store while material is not in stock yet: when it expects to have it.
            $table->date('expected_stock_date')->nullable()->after('ready_by');
        });
    }

    public function down()
    {
        Schema::table('portal_orders', function (Blueprint $table) {
            $table->dropColumn('expected_stock_date');
        });
    }
}
