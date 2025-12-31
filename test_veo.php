<?php
$key = 'sk-a89a9f4688774de9828330345bc96a53';
$url = 'https://veo3api.com/v1/chat/completions';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'gpt-3.5-turbo', // Try common model name for proxy
    'messages' => [['role' => 'user', 'content' => 'Hello, are you online?']]
]));

echo "Testing URL: $url\n";
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "Status: " . $info['http_code'] . "\n";
echo "Response: " . $response . "\n";
