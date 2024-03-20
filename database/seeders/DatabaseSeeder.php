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
        // \App\Models\User::factory(10)->create();

        \App\Models\BillType::factory()->create([
            'label' => 'Food'
        ]);

        \App\Models\BillType::factory()->create([
            'label' => 'Transport'
        ]);

        \App\Models\BillType::factory()->create([
            'label' => 'Fuel'
        ]);
        
        \App\Models\BillType::factory()->create([
            'label' => 'Parking Fee'
        ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
