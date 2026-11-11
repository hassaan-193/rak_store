<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomingMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoming_materials', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_time');
            $table->enum('source_type', ['driver', 'supplier']);
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->enum('project_type', ['general', 'project']);
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('engineer_name');
            $table->string('store_location')->nullable();
            $table->string('dn_number');
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoming_materials');
    }
}
