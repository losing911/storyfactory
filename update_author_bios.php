<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$authors = \App\Models\Author::whereNull('bio')->orWhere('bio', '')->get();

echo "Updating bios for " . $authors->count() . " authors...\n";

$roles = [
    'Netrunner' => "An elite hacker navigating the deep web of Neo-Pera. Looking for the truth hidden in the data packets.",
    'Street Samurai' => "Mercenary for hire. Blade is sharper than the corporate lies. Surviving one contract at a time.",
    'Corporate Spy' => "Infiltrating the mega-corps from within. Every secret has a price, and business is good.",
    'Tech-Ripper' => "Salvaging parts from the fallen androids to build something better. The streets are a goldmine.",
    'Glitch Artist' => "Turning system errors into visual masterpieces. Reality is just a canvas waiting to be broken."
];

foreach ($authors as $author) {
    if (isset($roles[$author->role])) {
        $author->bio = $roles[$author->role];
    } else {
        $author->bio = "A ghost in the machine. Little is known about this entity besides their digital footprint in the Neo-Pera archives.";
    }
    $author->save();
    echo "- Updated: {$author->name}\n";
}

echo "Done.\n";
