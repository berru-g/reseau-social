<?php
require_once '../config.php';
require_once '../functions.php';

if (!isLoggedIn()) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    try {
        // Vérifier que le fichier appartient bien à l'utilisateur
        $stmt = $pdo->prepare("SELECT file_path, user_id FROM user_files WHERE id = ?");
        $stmt->execute([$_POST['file_id']]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file || $file['user_id'] != $_SESSION['user_id']) {
            throw new Exception("Fichier non trouvé ou accès refusé");
        }
        
        // Supprimer le fichier physique
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        
        // Supprimer l'entrée en base de données
        $stmt = $pdo->prepare("DELETE FROM user_files WHERE id = ?");
        $stmt->execute([$_POST['file_id']]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
}