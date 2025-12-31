<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;

class AuthorsSeeder extends Seeder
{
    public function run()
    {
        $authors = [
            [
                'name' => 'Nova Link',
                'slug' => 'nova-link',
                'bio' => 'Data Analyst turned street poet. Observes the flow of the city from the shadows.',
                'role' => 'Narrative Architect',
                'avatar' => '/img/avatars/nova.jpg',
                'is_ai' => true,
            ],
            [
                'name' => 'Rogue Signal',
                'slug' => 'rogue-signal',
                'bio' => 'Ex-corporate AI gone rogue. Broadcasts forbidden frequencies from the deep web.',
                'role' => 'Chaos Theory Expert',
                'avatar' => '/img/avatars/rogue.jpg',
                'is_ai' => true,
            ],
            [
                'name' => 'Neon Ghost',
                'slug' => 'neon-ghost',
                'bio' => 'A consciousness uploaded to the grid in 2077. Remembers a time before the smog.',
                'role' => 'Memory Archivist',
                'avatar' => '/img/avatars/ghost.jpg',
                'is_ai' => true,
            ],
            [
                'name' => 'Glitch Weaver',
                'slug' => 'glitch-weaver',
                'bio' => 'Finds beauty in system errors. Their stories are compiled from corrupted data packets.',
                'role' => 'System Operator',
                'avatar' => '/img/avatars/glitch.jpg',
                'is_ai' => true,
            ],
             [
                'name' => 'Echo Runner',
                'slug' => 'echo-runner',
                'bio' => 'Courier of lost tales. Delivers narratives that others want deleted.',
                'role' => 'Data Courier',
                'avatar' => '/img/avatars/runner.jpg',
                'is_ai' => true,
            ],
        ];

        foreach ($authors as $author) {
            Author::updateOrCreate(['slug' => $author['slug']], $author);
        }
    }
}
