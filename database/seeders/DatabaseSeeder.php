<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\AccessKeys::create([
            'access_key' => '$2a$12$A9KPTxrpodVL88bmqLHwme8.9/LlgtHWKT.xdFYKrpD9QYatyAcse',
            'role' => 'admin',
        ]);
    }
}
