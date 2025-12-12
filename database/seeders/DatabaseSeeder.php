<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LoreSeeder::class,
        ]);
        
        // Ensure Admin exists (optional if deploy script handles it, but good to have)
        /*
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@anxipunk.icu',
        ]);
        */
    }
}
