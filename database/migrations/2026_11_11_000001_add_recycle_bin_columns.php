<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecycleBinColumns extends Migration
{
    private $tables = [
        'incoming_materials', 'incoming_material_items',
        'outgoing_materials', 'outgoing_material_items',
        'return_materials', 'return_material_items',
        'projects', 'suppliers', 'drivers',
        'tools', 'tool_assignments', 'tool_returns',
        'users',
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
                $t->unsignedBigInteger('deleted_by')->nullable();
                $t->string('deletion_reason')->nullable();
                $t->index('deleted_at');
            });
        }
    }

    public function down()
    {
        // Intentionally empty: dropping these columns would permanently destroy Recycle Bin data.
    }
}
