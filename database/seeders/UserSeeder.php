<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create one specific user
        $admin =User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin@gmail.com'),
            'email_verified_at' => now(),
        ]);

        // Assign super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $admin->assignRole($superAdminRole);
        }

        User::create([
            'name' => 'User1',
            'email' => 'user1@gmail.com',
            'password' => bcrypt('user1@gmail.com'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'User2',
            'email' => 'user2@gmail.com',
            'password' => bcrypt('user2@gmail.com'),
            'email_verified_at' => now(),
        ]);



    }
}
