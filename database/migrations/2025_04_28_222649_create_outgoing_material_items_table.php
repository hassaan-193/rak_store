<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutgoingMaterialItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outgoing_material_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outgoing_material_id'); // Reference to outgoing material record
            $table->string('item_name'); // Name of the material being dispatched
            $table->string('category'); // Category of the material
            $table->string('unit'); // Unit of the material (e.g., kg, meter)
            $table->decimal('quantity', 10, 2); // Quantity of the material being dispatched
            $table->timestamps(); // Created and updated timestamps
    
            // Foreign key constraint
            $table->foreign('outgoing_material_id')->references('id')->on('outgoing_materials')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoing_material_items');
    }
}
