<?php
$path = '.env';
$content = file_get_contents($path);

// Define replacements
$replacements = [
    'APP_URL' => 'http://anxipunk.icu',
    'DB_DATABASE' => 'DBDATABASE',
    'DB_USERNAME' => 'DBUSERNAME',
    'DB_PASSWORD' => 'DBPASSWORD',
];

// Perform replacements
foreach ($replacements as $key => $value) {
    // Quote string if it contains special chars (like the password)
    $valStr = str_contains($value, '^') || str_contains($value, ' ') ? '"'.$value.'"' : $value;
    
    if (preg_match("/^{$key}=.*/m", $content)) {
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$valStr}", $content);
    } else {
        $content .= "\n{$key}={$valStr}";
    }
}

file_put_contents($path, $content);
echo "Updated .env successfully.\n";
