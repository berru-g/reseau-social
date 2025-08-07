<?php
require_once  '../includes/config.php';
require_once  '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$user = getUserById($_SESSION['user_id']);

require_once  '../includes/header.php';
?>

<div class="container profile-container">
    <h2><?= htmlspecialchars($user['username']) ?></h2>
    
    <div class="profile-info">
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    </div>
    
<!--template à remplir ici -->


  </div>


  <script src="../assets/js/template.js"></script>


<?php require_once  '../includes/footer.php'; ?>