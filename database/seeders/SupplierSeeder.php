<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'Acme Supplies Co.',
                'phone' => '123-456-7890',
                'email' => 'john@acmesupplies.com',
                'address' => '123 Main St, Cityville',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Global Traders Ltd.',
                'phone' => '555-987-6543',
                'email' => 'jane@globaltraders.com',
                'address' => '456 Market Rd, Townsville',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quick Parts Inc.',
                'phone' => '777-321-4567',
                'email' => 'alan@quickparts.com',
                'address' => '789 Industrial Park, Tech City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
