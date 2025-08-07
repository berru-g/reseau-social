<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if (!isLoggedIn()) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    try {
        // Vérification de propriété
        $stmt = $pdo->prepare("SELECT id FROM user_files WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['file_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Fichier non trouvé ou accès refusé");
        }
        
        // Basculer le statut
        $stmt = $pdo->prepare("UPDATE user_files SET is_public = NOT is_public WHERE id = ?");
        $stmt->execute([$_POST['file_id']]);
        
        // Récupérer le nouveau statut
        $stmt = $pdo->prepare("SELECT is_public FROM user_files WHERE id = ?");
        $stmt->execute([$_POST['file_id']]);
        $result = $stmt->fetch();
        
        echo json_encode(['success' => true, 'is_public' => (bool)$result['is_public']]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
}