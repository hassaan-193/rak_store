<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePoReceivingTables extends Migration
{
    public function up()
    {
        Schema::create('portal_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portal_order_id');
            $table->string('material_name');
            $table->string('item_code', 100)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity_ordered', 14, 3)->default(0);
            $table->timestamps();

            $table->foreign('portal_order_id')->references('id')->on('portal_orders');
            $table->index('portal_order_id');
        });

        // Delivery log. Rows are never deleted, only voided (no SoftDeletes, no cascade).
        Schema::create('po_receipt_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portal_order_id');
            $table->unsignedBigInteger('portal_order_line_id');
            $table->string('type', 20); // store_receipt | direct_to_site | from_stock
            $table->decimal('quantity', 14, 3);
            $table->date('entry_date');
            $table->string('vendor_dn_number', 100)->nullable();
            $table->string('site_receiver_name')->nullable();
            $table->string('dn_file_path')->nullable();
            $table->unsignedBigInteger('source_stock_item_id')->nullable();
            $table->unsignedBigInteger('stock_item_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->timestamps();

            $table->foreign('portal_order_id')->references('id')->on('portal_orders');
            $table->foreign('portal_order_line_id')->references('id')->on('portal_order_lines');
            $table->index(['portal_order_id', 'voided_at']);
        });

        Schema::table('portal_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('event_seq')->default(0);
            $table->boolean('legacy_flow')->default(0);
        });

        Schema::table('incoming_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('portal_order_id')->nullable();
        });

        // Data step: orders the old dispatch flow already delivered are kept as "legacy"
        // (no entries are invented); everything else waits for receipts.
        DB::table('portal_orders')->where('status', 'delivered')->update(['legacy_flow' => 1]);
        DB::table('portal_orders')->whereIn('status', ['awaiting_dispatch', 'received', 'scheduled', 'driver_assigned', 'collected'])->update(['status' => 'received']);

        foreach (DB::table('portal_orders')->get() as $order) {
            $items = json_decode($order->items ?: '[]', true) ?: [];
            foreach ($items as $item) {
                if (empty($item['material_name'])) {
                    continue;
                }
                DB::table('portal_order_lines')->insert([
                    'portal_order_id' => $order->id,
                    'material_name' => $item['material_name'],
                    'item_code' => $item['item_code'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'quantity_ordered' => $item['quantity'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Permission for recording / voiding deliveries.
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'po-receive', 'guard_name' => 'web']);
        foreach (['super-admin', 'approver'] as $roleName) {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role && !$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        // Intentionally empty: delivery records must never be dropped by a rollback.
    }
}
