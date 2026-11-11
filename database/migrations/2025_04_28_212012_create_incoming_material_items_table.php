<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomingMaterialItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incoming_material_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incoming_material_id');
            $table->string('item_name');
            $table->string('category');
            $table->string('brand')->nullable();
            $table->string('unit');
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
    
            // Foreign key constraint
            $table->foreign('incoming_material_id')->references('id')->on('incoming_materials')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoming_material_items');
    }
}
