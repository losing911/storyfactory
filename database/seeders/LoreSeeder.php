<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoreEntry;
use Illuminate\Support\Str;

class LoreSeeder extends Seeder
{
    public function run(): void
    {
        // City
        LoreEntry::create([
            'title' => 'Neo-Pera',
            'slug' => Str::slug('Neo-Pera'),
            'type' => 'city',
            'description' => 'A dystopian megacity rising from the ruins of old Istanbul. Dominated by neon-drenched skyscrapers, constant acid rain, and a sharp divide between the gleaming Upper Towers and the chaotic Lower Districts.',
            'is_active' => true
        ]);

        // Character: Delfin
        LoreEntry::create([
            'title' => 'Delfin',
            'slug' => Str::slug('Delfin'),
            'type' => 'character',
            'description' => 'Resistance leader and prodigy. Possesses high intelligence, expert mastery of martial arts and firearms, and advanced hacking skills. She operates from the shadows, dismantling corporate control one node at a time.',
            'is_active' => true
        ]);
        
        // Placeholder Faction
        LoreEntry::create([
            'title' => 'The Syndicate',
            'slug' => 'the-syndicate',
            'type' => 'faction',
            'description' => 'The ruling corporate entity controlling Neo-Pera\'s water and data supply.',
            'is_active' => true
        ]);
    }
}
