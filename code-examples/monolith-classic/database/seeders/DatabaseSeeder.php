<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('customers')->insert([
            ['name' => 'Nigar Əliyeva', 'email' => 'nigar@example.com', 'phone' => '+994501234567', 'address' => 'Baku, Nizami street 15', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rəşad Məmmədov', 'email' => 'rashad@example.com', 'phone' => '+994551234567', 'address' => 'Baku, 28 May street 25', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Leyla Həsənova', 'email' => 'leyla@example.com', 'phone' => '+994701234567', 'address' => 'Baku, Neftchilar avenue 40', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('products')->insert([
            ['name' => 'Toyuq Plov', 'description' => 'Traditional Azerbaijani pilaf with chicken', 'price' => 12.00, 'is_available' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Qoyun Plov', 'description' => 'Traditional Azerbaijani pilaf with lamb', 'price' => 15.00, 'is_available' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Şah Plov', 'description' => 'Royal pilaf with dried fruits and nuts', 'price' => 18.00, 'is_available' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Balıq Plov', 'description' => 'Pilaf with fish', 'price' => 14.00, 'is_available' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
