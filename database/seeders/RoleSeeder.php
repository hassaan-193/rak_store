<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Import the Permission model

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // Attach all permissions to super-admin
        $permissions = Permission::all();
        $superAdminRole->syncPermissions($permissions);
    }
}
