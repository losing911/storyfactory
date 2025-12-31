<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Story;
use App\Models\LoreEntry;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap and ping search engines';

    public function handle()
    {
        $this->info('Generating Sitemap...');
        
        $sitemap = Sitemap::create();

        // Static Pages
        $sitemap->add(Url::create('/')->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
        $sitemap->add(Url::create('/gallery')->setPriority(0.8));
        $sitemap->add(Url::create('/about')->setPriority(0.5));
        $sitemap->add(Url::create('/lore')->setPriority(0.8));

        // Stories
        Story::where('durum', 'published')->each(function (Story $story) use ($sitemap) {
            $sitemap->add(
                Url::create(route('story.show', $story->slug))
                    ->setLastModificationDate($story->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.9)
            );
        });

        // Lore Entries
        LoreEntry::where('is_active', true)->each(function (LoreEntry $lore) use ($sitemap) {
            $sitemap->add(
                Url::create(route('lore.show', $lore->slug))
                    ->setLastModificationDate($lore->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.7)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('Sitemap generated successfully at public/sitemap.xml');
    }
}
