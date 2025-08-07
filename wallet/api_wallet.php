<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

$pdo = getDB();
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    if ($requestMethod === 'GET' && isset($_GET['action'])) {
        $action = $_GET['action'];
        $userId = (int)$_GET['user_id'];
        
        if ($action === 'get') {
            $stmt = $pdo->prepare("SELECT * FROM user_crypto_holdings WHERE user_id = ?");
            $stmt->execute([$userId]);
            $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($holdings);
            exit;
        }
    }
    
    if ($requestMethod === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'];
        $userId = (int)$input['user_id'];
        
        if ($action === 'add') {
            $cryptoId = $input['crypto_id'];
            $cryptoName = $input['crypto_name'];
            $purchasePrice = (float)$input['purchase_price'];
            $quantity = (float)$input['quantity'];
            
            // Vérifie si la crypto existe déjà
            $stmt = $pdo->prepare("SELECT id FROM user_crypto_holdings WHERE user_id = ? AND crypto_id = ?");
            $stmt->execute([$userId, $cryptoId]);
            
            if ($stmt->fetch()) {
                // Mise à jour si existe
                $stmt = $pdo->prepare("UPDATE user_crypto_holdings SET quantity = ?, purchase_price = ? WHERE user_id = ? AND crypto_id = ?");
                $stmt->execute([$quantity, $purchasePrice, $userId, $cryptoId]);
            } else {
                // Insertion si nouvelle
                $stmt = $pdo->prepare("INSERT INTO user_crypto_holdings (user_id, crypto_id, crypto_name, purchase_price, quantity) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $cryptoId, $cryptoName, $purchasePrice, $quantity]);
            }
            
            // Enregistre un snapshot du wallet
            updateWalletSnapshot($userId, $pdo);
            
            echo json_encode(['success' => true]);
            exit;
        }
        
        if ($action === 'delete') {
            $cryptoId = $input['crypto_id'];
            
            $stmt = $pdo->prepare("DELETE FROM user_crypto_holdings WHERE user_id = ? AND crypto_id = ?");
            $stmt->execute([$userId, $cryptoId]);
            
            // Met à jour le snapshot après suppression
            updateWalletSnapshot($userId, $pdo);
            
            echo json_encode(['success' => true]);
            exit;
        }
    }
    
    http_response_code(400);
    echo json_encode(['error' => 'Action non valide']);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}

function updateWalletSnapshot($userId, $pdo) {
    // Calcule la valeur totale du wallet
    $stmt = $pdo->prepare("
        SELECT SUM(purchase_price * quantity) as total 
        FROM user_crypto_holdings 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $total = $stmt->fetchColumn();
    
    // Insère ou met à jour le snapshot
    $stmt = $pdo->prepare("
        INSERT INTO wallet_history (user_id, total_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE total_value = ?
    ");
    $stmt->execute([$userId, $total, $total]);
}