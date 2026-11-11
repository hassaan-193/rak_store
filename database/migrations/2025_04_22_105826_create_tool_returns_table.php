<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateToolReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tool_returns', function (Blueprint $table) {
            $table->id();

            // Link to the original tool assignment
            $table->foreignId('tool_assignment_id')->constrained()->onDelete('cascade');

            // Return-specific fields
            $table->string('return_condition');
            $table->date('return_date');
            $table->integer('quantity_returned');

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
        Schema::dropIfExists('tool_returns');
    }
}
