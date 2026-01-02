<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use App\Models\Author;

class AssignAuthors extends Command
{
    protected $signature = 'app:assign-authors {--force}';
    protected $description = 'Assigns random authors to stories that have no author_id.';

    public function handle()
    {
        $count = Story::whereNull('author_id')->count();
        $this->info("Found $count stories without an author.");

        if ($count === 0 && !$this->option('force')) {
            $this->info("Nothing to do.");
            return;
        }

        // Ensure we have authors
        if (Author::count() === 0) {
            $this->info("Creating default Netrunners...");
            $defaults = [
                ['name' => 'Nexus Ryder', 'role' => 'Netrunner', 'slug' => 'nexus-ryder', 'bio' => 'Hacking the mainframe since 2066.'],
                ['name' => 'Valkyrie 7', 'role' => 'Street Samurai', 'slug' => 'valkyrie-7', 'bio' => 'Blade mechanism expert.'],
                ['name' => 'Ghost Interface', 'role' => 'Glitch Artist', 'slug' => 'ghost-interface', 'bio' => 'Data is the new flesh.'],
            ];
            foreach ($defaults as $data) {
                Author::create($data);
            }
        }

        $authors = Author::all();
        $stories = Story::whereNull('author_id')->get();

        $bar = $this->output->createProgressBar($stories->count());
        $bar->start();

        foreach ($stories as $story) {
            $story->author_id = $authors->random()->id;
            $story->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Success! All stories now have an author.");
    }
}
