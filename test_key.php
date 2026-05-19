<?php
$key = 'snfx-WHjsJKPaZuAedRfS2HOSkdPhsnS1ffq5HoObxoy5pJ7ib';
$baseUrl = 'https://core.snifoxai.com/v1';

// 1x1 white pixel JPEG base64
$pixel = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AJQAB/9k=';

$models = [
    'anthropic/claude-opus-4.6',
    'anthropic/claude-sonnet-4.5',
    'google/gemini-2.5-flash',
    'google/gemini-3-flash-preview',
    'openai/gpt-5.1',
    'openai/gpt-5.5',
    'openai/gpt-5-mini',
];

foreach ($models as $model) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $key,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => $model,
        'messages' => [['role' => 'user', 'content' => [
            ['type' => 'image_url', 'image_url' => ['url' => 'data:image/jpeg;base64,' . $pixel]],
            ['type' => 'text', 'text' => 'Describe this image in exactly 3 words.']
        ]]],
        'max_tokens' => 20,
        'temperature' => 0,
    ]));
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($res, true);
    $reply = $data['choices'][0]['message']['content'] ?? ($data['error']['message'] ?? json_encode($data));
    $lower = strtolower($reply);
    $seesImage = $code === 200 && !str_contains($lower, "don't see") && !str_contains($lower, 'no image') && !str_contains($lower, 'cannot see');
    
    $status = $code === 200 ? ($seesImage ? "✓ VISION OK" : "~ TEXT ONLY") : "✗ ERROR $code";
    echo "[$status] $model\n";
    echo "   " . substr($reply, 0, 100) . "\n\n";
}
