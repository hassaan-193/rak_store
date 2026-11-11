<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_materials', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_time'); 
            $table->unsignedBigInteger('driver_id'); 
            $table->enum('project_type', ['general', 'project']); 
            $table->unsignedBigInteger('project_id')->nullable(); 
            $table->string('engineer_name'); 
            $table->string('store_location')->nullable();
            $table->string('dn_number'); 
            $table->timestamps(); 
    
            // Foreign key constraints
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoing_materials');
    }
}
