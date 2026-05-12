<?php
$response = file_get_contents('https://openrouter.ai/api/v1/models');
$data = json_decode($response, true);

echo "=== ALL FREE MODELS WITH VISION ===\n\n";
foreach ($data['data'] as $model) {
    $id = $model['id'];
    $promptPrice = $model['pricing']['prompt'] ?? '?';
    // Check for free (price is "0" or 0)
    if ($promptPrice == 0 || $promptPrice === '0' || $promptPrice === '0.0') {
        $modality = $model['architecture']['modality'] ?? [];
        $inputTypes = is_array($modality) && isset($modality['input']) ? (is_array($modality['input']) ? implode(',', $modality['input']) : $modality['input']) : 'unknown';
        if (stripos($inputTypes, 'image') !== false) {
            echo "ID: {$id} | Input: {$inputTypes} | Price: {$promptPrice}\n";
        }
    }
}
echo "\n=== GEMINI MODELS (ALL) ===\n\n";
foreach ($data['data'] as $model) {
    $id = $model['id'];
    if (stripos($id, 'gemini') !== false) {
        $promptPrice = $model['pricing']['prompt'] ?? '?';
        echo "ID: {$id} | Price: {$promptPrice}\n";
    }
}
