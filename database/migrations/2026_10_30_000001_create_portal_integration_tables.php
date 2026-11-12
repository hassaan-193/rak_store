<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortalIntegrationTables extends Migration
{
    public function up()
    {
        // tables may already exist on servers set up before migrations were tracked
        if (! Schema::hasTable('portal_orders')) {
        Schema::create('portal_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portal_po_id')->unique();
            $table->string('po_number')->nullable();
            $table->unsignedBigInteger('portal_project_id')->nullable();
            $table->string('project_name')->nullable();
            $table->unsignedBigInteger('project_id')->nullable(); // matched store project
            $table->string('supplier_name')->nullable();
            $table->date('required_date')->nullable();
            $table->json('items')->nullable();
            // received -> scheduled -> driver_assigned -> collected -> delivered (or cancelled)
            $table->string('status')->default('received');
            $table->date('expected_delivery_date')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('delivery_note_no')->nullable();
            $table->string('delivery_note_file')->nullable();
            $table->string('received_by')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('store_notes')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
        });
        }

        if (! Schema::hasTable('integration_messages')) {
        Schema::create('integration_messages', function (Blueprint $table) {
            $table->id();
            $table->string('direction', 3); // in / out
            $table->string('message_id', 64);
            $table->string('event')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('response')->nullable();
            $table->string('status')->default('pending'); // pending, done, failed, rejected
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamps();

            $table->unique(['direction', 'message_id']);
        });
        }
    }

    public function down()
    {
        Schema::dropIfExists('integration_messages');
        Schema::dropIfExists('portal_orders');
    }
}
