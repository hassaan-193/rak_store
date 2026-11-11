<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyWorkerFieldInToolAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            // Add new column
            $table->string('worker_name')->nullable()->after('item_code');

            // Optional: drop foreign key and column
            $table->dropForeign(['worker_id']);
            $table->dropColumn('worker_id');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            // Revert changes
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->dropColumn('worker_name');
        });
    }
}
