<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateToolAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tool_assignments', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            // Redundant info - captured for historical records (denormalized)
            $table->string('item_name');
            $table->string('category');
            $table->string('item_code');
    
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->string('project_name');
    
            $table->integer('quantity_issued');
            $table->string('purpose');
            $table->dateTime('assignment_date');
            $table->string('condition_before_issue');
    
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
        Schema::dropIfExists('tool_assignments');
    }
}
