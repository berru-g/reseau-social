<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pen_id'])) {
    $penId = $_POST['pen_id'];
    $userId = $_SESSION['user_id'];
    
    // Vérifie que le pen appartient bien à l'utilisateur
    $stmt = $pdo->prepare("DELETE FROM user_pens WHERE id = ? AND user_id = ?");
    $stmt->execute([$penId, $userId]);
    
    $_SESSION['success_message'] = "Pen supprimé avec succès";
}

header("Location: " . BASE_URL . "/pages/profile.php");
exit;