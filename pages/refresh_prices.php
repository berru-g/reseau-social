<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(403);
    exit;
}

$cacheFile = '../cache/agricultural_prices.json';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
}

// Nouvelle vérification
$testUrl = "https://data.economie.gouv.fr/api/records/1.0/search/?dataset=prix-producteurs-agricoles&rows=1";
if (file_get_contents($testUrl) === false) {
    echo json_encode(['error' => 'API indisponible']);
    exit;
}

echo json_encode(['success' => true]);