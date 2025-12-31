<?php
$key = 'sk-a89a9f4688774de9828330345bc96a53';
$url = 'https://veo3api.com/generate';

$data = [
    "prompt" => "A cinematic shot of a cyberpunk city street at night, neon lights reflecting on wet pavement, hyper-realistic, 8k",
    "model" => "veo3-fast",
    "watermark" => "veo"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

echo "Sending Video Request to Veo3...\n";
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "Status: " . $info['http_code'] . "\n";
echo "Response: " . $response . "\n";
