<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoreEntry;

class VultureSeeder extends Seeder
{
    public function run()
    {
        // Add Vulture if not exists
        if (!LoreEntry::where('title', 'Vulture')->exists()) {
             LoreEntry::create([
                'title' => 'Vulture',
                'slug' => 'vulture',
                'type' => 'city', // Assuming user implies it's a location
                'description' => 'A scavengers paradise, built purely from the scrap of fallen mega-corporations. A vertical slum of rust and neon.',
                'visual_prompt' => 'Vertical slum city made of scrap metal and rust, cyberpunk scavenger city, dusty, industrial wasteland, mad max meets cyberpunk',
                'is_active' => true,
                'image_url' => null // Will be filled by the command above
            ]);
            $this->command->info("Vulture added to Lore.");
        } else {
            $this->command->info("Vulture already exists.");
        }
    }
}
