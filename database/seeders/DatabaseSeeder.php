<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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


        \App\Models\Products::create([
            'token' => '8e478edf-9c55-4ef9-ae46-f91ddd5d285f',
            'image1' => 'http://localhost:8000/images/store/products/1701178905.jpg',
            'image2' => 'http://localhost:8000/images/store/products/1701178905.jpg',
            'name' => 'WEbina Digital Application',
            'rating' => 5,
            'views' => 152,
            'purchases' => 20,
            'downloads' => 45,
            'status' => 'active',
            'category' => 'Website',
            'price' => 299,
            'old_price' => 400,
            'description' => 'vMaintenance: Proper care is essential to maintain the quality and appearance of jeans. Washing and drying instructions may vary depend',
            'tags' => 'Clothing, Jeans, Trousers',
            'publisher' => 'WEBINA DIGITAL',
            'link' => 'https://applicationtest.com/',
            'pdf' => '8e478edf-9c55-4ef9-ae46-f91ddd5d285f',
            'last_updated' => Carbon::now()
        ]);


        \App\Models\User::create([
            'name' => env('ADMINISTRATION_INIT_NAME'),
            'email' => env('ADMINISTRATION_INIT_EMAIL'),
            'password' => Hash::make(env('ADMINISTRATION_INIT_PASSWORD')),
            'avatar' => env('APP_URL') . '/users/avatar/avatar9.png',
            'email_verified_at' => now(),
            'phone' => env('ADMINISTRATION_INIT_PHONE'),
            'role' => 'admin',
            'status' => 'active'
        ]);
    }
}
