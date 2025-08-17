<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$sql_content = $_POST['sql_content'] ?? '';
$file_name = cleanFileName($_POST['file_name'] ?? '');

// Validation
if (empty($sql_content)) {
    echo json_encode(['success' => false, 'message' => 'Contenu SQL vide']);
    exit;
}

if (empty($file_name)) {
    echo json_encode(['success' => false, 'message' => 'Nom de fichier invalide']);
    exit;
}

// Chemin de sauvegarde
$user_dir = "../public/users/$user_id/";
if (!file_exists($user_dir)) {
    mkdir($user_dir, 0755, true);
}

$file_path = $user_dir . $file_name;

try {
    // Sauvegarde physique
    file_put_contents($file_path, $sql_content);
    
    // Enregistrement en BDD
    $stmt = $pdo->prepare("INSERT INTO user_files (user_id, file_name, file_path, file_type) 
                          VALUES (?, ?, ?, 'sql')");
    $stmt->execute([$user_id, $file_name, $file_path]);
    
    echo json_encode(['success' => true, 'path' => $file_path]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function cleanFileName($name) {
    return preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $name);
}