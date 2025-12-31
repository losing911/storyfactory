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
        LoreEntry::updateOrCreate(
            ['slug' => Str::slug('Neo-Pera')],
            [
                'title' => 'Neo-Pera',
                'type' => 'city',
                'description' => 'Eski İstanbul\'un kalıntıları üzerinde yükselen distopik bir mega şehir. Neon ışıklı gökdelenlerin, sürekli yağan asit yağmurlarının ve Parlak Kuleler ile kaotik Alt Bölgeler arasındaki keskin ayrımın hüküm sürdüğü yer.',
                'is_active' => true,
                'keywords' => ['İstanbul', 'Megacity', 'The City']
            ]
        );

        // Character: Delfin
        LoreEntry::updateOrCreate(
            ['slug' => Str::slug('Delfin')],
            [
                'title' => 'Delfin',
                'type' => 'character',
                'description' => 'Direniş lideri ve dahi bir hacker. Üstün zekası, dövüş sanatlarındaki ustalığı ve veri sızma yetenekleriyle bilinir. Gölgelerden hareket ederek Şirket hakimiyetini her seferinde bir düğüm sökerek parçalar.',
                'is_active' => true,
                'keywords' => ['Direniş Lideri', 'Hacker', 'Resistance Leader']
            ]
        );
        
        // Faction: The Syndicate -> Sendika
        LoreEntry::updateOrCreate(
            ['slug' => 'sendika'], // Assuming 'sendika' or 'the-syndicate' - sticking to Turkish slug now
            [
                'title' => 'Sendika',
                'type' => 'faction',
                'description' => 'Neo-Pera\'nın su ve veri kaynaklarını kontrol eden, şehri demir yumrukla yöneten şirketler birliği.',
                'is_active' => true,
                'keywords' => ['The Syndicate', 'Şirketler Birliği', 'Corporation']
            ]
        );
    }
}
