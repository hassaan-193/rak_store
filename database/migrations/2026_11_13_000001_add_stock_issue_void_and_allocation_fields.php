<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-only. Adds: stock-issue audit + idempotency key on PO delivery entries, whole-PO void
 * fields, and the exact source rows an Outgoing approval deducted from (needed for reversal).
 * Existing orders, entries and stock are untouched.
 */
class AddStockIssueVoidAndAllocationFields extends Migration
{
    public function up()
    {
        Schema::table('po_receipt_entries', function (Blueprint $table) {
            $table->json('stock_issue_details')->nullable();
            $table->string('submission_uuid', 64)->nullable();
            $table->unique(['portal_order_id', 'submission_uuid'], 'po_receipt_entries_po_submission_unique');
        });

        Schema::table('portal_orders', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->string('voided_by_name')->nullable();
        });

        Schema::table('outgoing_material_items', function (Blueprint $table) {
            $table->json('stock_allocations')->nullable();
            $table->timestamp('allocations_reversed_at')->nullable();
        });
    }

    public function down()
    {
        // Intentionally empty: audit and allocation records must never be dropped by a rollback.
    }
}
