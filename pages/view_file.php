<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
// Attention tout les json ne se mette pas en tableau et le telechargement est uniquement en json, non en pdf pour l'instant
if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/pages/search.php");
    exit;
}

$fileId = $_GET['id'];

// Vérifier les permissions
$stmt = $pdo->prepare("
    SELECT uf.*, u.email as owner_email 
    FROM user_files uf
    JOIN users u ON uf.user_id = u.id
    WHERE uf.id = ? AND (uf.is_public = TRUE OR uf.user_id = ?)
");
$stmt->execute([$fileId, $_SESSION['user_id']]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    header("Location: " . BASE_URL . "/pages/search.php?error=file_not_found");
    exit;
}

// Logique d'affichage selon le type de fichier
$fileContent = '';
try {
    switch ($file['file_type']) {
        case 'csv':
            $fileContent = displayCsvFile($file['file_path']);
            break;
        case 'excel':
            $fileContent = displayExcelFile($file['file_path']);
            break;
        case 'json':
            $fileContent = displayJsonFile($file['file_path']);
            break;
        default:
            $fileContent = '<pre>' . htmlspecialchars(file_get_contents($file['file_path'])) . '</pre>';
    }
} catch (Exception $e) {
    $fileContent = '<div class="alert alert-danger">Erreur lors de la lecture du fichier: ' .
        htmlspecialchars($e->getMessage()) . '</div>';
}

require_once '../includes/header.php';
?>

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= htmlspecialchars($file['file_name']) ?></h2>
        <a href="gallery.php" class="btn btn-success">
            <!--bug avec <a href="#" class="primary-btn back-btn" data-fallback="search.php">-->
            <i class="fa-solid fa-reply"></i>
        </a>
        <a href="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>" download class="btn btn-success"><i
                class="fas fa-download"></i></a>

    </div>

    <div class="file-meta mb-4">
        <p><strong>Propriétaire:</strong> <?= htmlspecialchars($file['owner_email']) ?></p>
        <p><strong>Date d'upload:</strong> <?= date('d/m/Y H:i', strtotime($file['upload_date'])) ?></p>
        <p><strong>Type:</strong> <span class="badge badge-<?= $file['file_type'] ?>">
                <?= strtoupper($file['file_type']) ?>
            </span></p>
    </div>

    <div class="file-content card">
        <div class="card-body">
            <?= $fileContent ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>