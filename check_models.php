<?php

require __DIR__ . '/vendor/autoload.php';

$apiKey = env('GEMINI_API_KEY');

if (!$apiKey) {
    // try reading .env manually if env() helper isn't ready in this standalone script
    $envContent = file_get_contents(__DIR__ . '/.env');
    preg_match('/GEMINI_API_KEY=(.*)/', $envContent, $matches);
    $apiKey = trim($matches[1] ?? '');
}

if (!$apiKey) {
    die("API Key not found in .env\n");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

echo "Querying: $url\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (isset($data['models'])) {
    echo "Available Models:\n";
    foreach ($data['models'] as $model) {
        echo " - " . $model['name'] . " (" . implode(', ', $model['supportedGenerationMethods'] ?? []) . ")\n";
    }
} else {
    echo "No models found or error parsing JSON.\n";
    echo $response;
}
