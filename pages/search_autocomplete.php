<?php
require_once '../../config.php';
require_once '../../functions.php';

if (!isLoggedIn()) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT uf.file_name as label, u.email as owner_email, uf.id as value
    FROM user_files uf
    JOIN users u ON uf.user_id = u.id
    WHERE (uf.file_name LIKE :term OR u.email LIKE :term)
    AND (uf.is_public = TRUE OR uf.user_id = :user_id)
    LIMIT 10
");
$stmt->execute([
    'term' => '%' . $term . '%',
    'user_id' => $_SESSION['user_id']
]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format pour l'autocomplétion
$suggestions = [];
foreach ($results as $row) {
    $suggestions[] = [
        'label' => $row['label'] . ' (Propriétaire: ' . $row['owner_email'] . ')',
        'value' => $row['label']
    ];
}

header('Content-Type: application/json');
echo json_encode($suggestions);
