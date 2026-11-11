<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnMaterialItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_material_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->string('category');
            $table->string('unit');
            $table->decimal('quantity', 8, 2);
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
        Schema::dropIfExists('return_material_items');
    }
}
