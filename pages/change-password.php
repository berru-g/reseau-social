<?php
require_once  '../includes/config.php';
require_once  '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$user = getUserById($_SESSION['user_id']);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($current_password)) {
        $errors[] = "Le mot de passe actuel est requis";
    }
    
    if (empty($new_password)) {
        $errors[] = "Le nouveau mot de passe est requis";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Les nouveaux mots de passe ne correspondent pas";
    }

    // Vérifier le mot de passe actuel
    if (empty($errors) && !password_verify($current_password, $user['password'])) {
        $errors[] = "Le mot de passe actuel est incorrect";
    }

    // Si pas d'erreurs, mettre à jour le mot de passe
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user['id']])) {
            $success = true;
        } else {
            $errors[] = "Une erreur est survenue lors de la mise à jour du mot de passe";
        }
    }
}

require_once  '../includes/header.php';
?>

<div class="container auth-container">
    <h2>Changer mon mot de passe</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Votre mot de passe a été mis à jour avec succès.
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="current_password">Mot de passe actuel:</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        
        <div class="form-group">
            <label for="new_password">Nouveau mot de passe:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirmer le nouveau mot de passe:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit">Mettre à jour</button>
    </form>
</div>

<?php require_once  '../includes/footer.php'; ?>