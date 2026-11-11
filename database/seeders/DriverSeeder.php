<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('drivers')->insert([
            [
                'name' => 'Michael Johnson',
                'phone' => '111-222-3333',
                'email' => 'john@gmail.com',
                'address' => '123 Main St, Cityville',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sarah Lee',
                'phone' => '111-222-3333',
                'email' => 'john@gmail.com',
                'address' => '123 Main St, Cityville',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Carlos Martinez',
                'phone' => '111-222-3333',
                'email' => 'john@gmail.com',
                'address' => '123 Main St, Cityville',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
