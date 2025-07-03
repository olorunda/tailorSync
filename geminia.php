<?php


$apiKey = 'AIzaSyBVpemolFRYQqCfKDiA-xciHJG-JIP78XY'; // 🔁 Replace this with your actual API key
$model = 'gemini-2.0-flash-preview-image-generation';
$model = 'gemini-2.0-flash-preview-image-generation';
$prompt = 'Create a 3D rendered image of a pig with wings and a top hat flying over a futuristic city, and describe it in one sentence.';

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

$headers = [
    "Content-Type: application/json",
    "X-Goog-Api-Key: {$apiKey}",
];

$body = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'responseModalities' => ['TEXT','IMAGE']
    ]
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => $headers,
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
    exit;
}
curl_close($ch);

$data = json_decode($response, true);

// Extract the image and text
$base64Image = null;
$descriptionText = null;

foreach ($data['candidates'][0]['content']['parts'] ?? [] as $part) {
    if (isset($part['inlineData']['data'])) {
        $base64Image = $part['inlineData']['data'];
    }
    if (isset($part['text'])) {
        $descriptionText = $part['text'];
    }
}

echo "Description: $descriptionText\n";

if ($base64Image) {
    file_put_contents('generated.png', base64_decode($base64Image));
    echo "✅ Image saved as generated.png\n";
} else {
    echo "❌ No image returned.\n";
}
