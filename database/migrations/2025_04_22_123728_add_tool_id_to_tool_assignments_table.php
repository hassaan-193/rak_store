<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToolIdToToolAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('tool_id')->nullable()->after('id'); // make it nullable for now
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
            //
        });
    }
}
