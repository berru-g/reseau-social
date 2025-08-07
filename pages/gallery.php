<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
// Prochaine feature :
// - upload plus de format
// - revoir le delete non fonctionnel
if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$user = getUserById($_SESSION['user_id']);

// Gestion de l'upload de fichiers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $allowedTypes = [
        'text/csv' => 'csv',
        'application/vnd.ms-excel' => 'excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
        'application/json' => 'json',
        'image/png' => 'image',
        'image/jpeg' => 'image',
        'image/jpg' => 'image'
    ];

    // Debug: Vérifiez ce qui est reçu
    error_log(print_r($_FILES, true));

    $fileType = $_FILES['uploaded_file']['type'];
    $fileExtension = strtolower(pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION));

    // Vérification plus robuste du type de fichier
    $valid = false;
    if (array_key_exists($fileType, $allowedTypes)) {
        $valid = true;
    } else {
        // Fallback pour certains navigateurs qui ne renvoient pas le bon type MIME
        $allowedExtensions = ['csv', 'xlsx', 'xls', 'json', 'png', 'jpg', 'jpeg'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $valid = true;
            // Mappage manuel des extensions
            $typeMap = [
                'csv' => 'csv',
                'xlsx' => 'excel',
                'xls' => 'excel',
                'json' => 'json',
                'png' => 'image',
                'jpg' => 'image',
                'jpeg' => 'image'
            ];
            $fileType = $typeMap[$fileExtension];
        }
    }

    if ($valid) {
        $uploadDir = '../uploads/' . $user['id'] . '/';

        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $_SESSION['error_message'] = "Erreur: Impossible de créer le dossier de destination.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }

        $originalName = basename($_FILES['uploaded_file']['name']);
        $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $originalName);
        $fileName = uniqid() . '_' . $safeName;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $filePath)) {
            // Enregistrement avec statut public par défaut
            $stmt = $pdo->prepare("INSERT INTO user_files 
                                 (user_id, file_name, file_path, file_type, is_public) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['id'],
                $originalName,
                $filePath,
                $allowedTypes[$fileType] ?? $fileType,
                1 // Public par défaut
            ]);

            $_SESSION['success_message'] = "Fichier uploadé avec succès!";
        } else {
            $errorMsg = "Erreur lors de l'upload. Code: " . $_FILES['uploaded_file']['error'];
            // Codes d'erreur PHP
            $uploadErrors = [
                1 => 'Taille maximale dépassée',
                2 => 'Taille maximale formulaire dépassée',
                3 => 'Upload partiel',
                4 => 'Aucun fichier',
                6 => 'Dossier temporaire manquant',
                7 => 'Échec écriture disque',
                8 => 'Extension PHP bloquée'
            ];
            $_SESSION['error_message'] = $uploadErrors[$_FILES['uploaded_file']['error']] ?? $errorMsg;
        }
    } else {
        $_SESSION['error_message'] = "Type de fichier non supporté. Formats acceptés: CSV, Excel, JSON.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Récupération des fichiers de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM user_files WHERE user_id = ? ORDER BY upload_date DESC");
$stmt->execute([$user['id']]);
$userFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container profile-container">

    <div class="profile-info">
        <h3><?= htmlspecialchars($user['username']) ?> Upload</h3>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="file-gallery">
            <!-- Case pour uploader un nouveau fichier -->
            <div class="file-card upload-card">
                <form method="post" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <label for="file-upload" class="upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>1 - Ajouter un fichier</span>
                        <input type="file" id="file-upload" name="uploaded_file"
                            accept=".csv,.xlsx,.xls,.json,.png,.jpg,.jpeg" required>
                    </label>
                    <!--<div class="form-group mt-2">
            <label class="lock-toggle">
            <input type="checkbox" id="make-public" name="is_public" hidden>
            <i class="fas fa-lock"></i>
        <span class="ml-2">2 - Rendre <span class="status-text"></span></span>
        </label>
        </div>-->
                    <div class="form-group form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="make-public" name="is_public" checked>
                        <label class="form-check-label" for="make-public">2 - Rendre public</label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2 w-100">3 - Uploader</button>
                    <div class="file-types mt-2">
                        <span class="badge badge-csv">.csv</span>
                        <span class="badge badge-excel">.xlsx</span>
                        <span class="badge badge-json">.json</span>
                        <span class="badge badge-image">.png</span>
                        <span class="badge badge-image">.jpg</span>
                    </div>
                </form>
            </div>

            <!-- Affichage des fichiers existants -->
            <?php foreach ($userFiles as $file): ?>
                <div class="file-card">
                    <?php if ($file['file_type'] === 'image'): ?>
                        <!-- Affiche l'image avec un aperçu -->
                        <div class="file-preview">
                            <a href="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>"
                                data-lightbox="image-<?= $file['id'] ?>">
                                <img src="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>"
                                    alt="<?= htmlspecialchars($file['file_name']) ?>" class="img-thumbnail">
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Garde les icônes pour les autres types de fichiers -->
                        <div class="file-icon">
                            <?php switch ($file['file_type']):
                                case 'csv': ?>
                                    <i class="fas fa-file-csv"></i>
                                    <?php break; ?>
                                <?php case 'excel': ?>
                                    <i class="fas fa-file-excel"></i>
                                    <?php break; ?>
                                <?php case 'json': ?>
                                    <i class="fas fa-file-code"></i>
                                    <?php break; ?>
                                <?php case 'googlesheet': ?>
                                    <i class="fab fa-google-drive"></i>
                                    <?php break; ?>
                                <?php default: ?>
                                    <i class="fas fa-file"></i>
                            <?php endswitch; ?>
                        </div>
                    <?php endif; ?>

                    <div class="file-info">
                        <h5><?= htmlspecialchars($file['file_name']) ?></h5>
                        <small><?= date('d/m/Y H:i', strtotime($file['upload_date'])) ?></small>
                        <small class="file-type <?= $file['file_type'] ?>"><?= strtoupper($file['file_type']) ?></small>
                    </div>
                    <div class="file-actions">
                        <a href="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>" download
                            class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i>

                            <a href="view_chart.php?id=<?= $file['id'] ?>" class="btn btn-info">
                                <i class="fas fa-chart-line"></i>
                            </a>

                            <button class="btn btn-sm btn-<?= $file['is_public'] ? 'success' : 'secondary' ?> toggle-share"
                                data-file-id="<?= $file['id'] ?>" title="<?= $file['is_public'] ? 'Public' : 'Privé' ?>">
                                <i class="fas fa-<?= $file['is_public'] ? 'lock-open' : 'lock' ?>"></i>
                            </button>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gestion de la suppression
        document.addEventListener('click', function (e) {
            if (e.target.closest('.delete-file')) {
                e.preventDefault();
                const button = e.target.closest('.delete-file');
                const fileId = button.getAttribute('data-file-id');

                if (confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
                    fetch('../includes/delete_file.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'file_id=' + fileId
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                button.closest('.file-card').remove();
                            } else {
                                alert('Erreur: ' + (data.message || 'Action échouée'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erreur réseau - Veuillez réessayer');
                        });
                }
            }

            // Gestion du partage public/privé
            if (e.target.closest('.toggle-share')) {
                const button = e.target.closest('.toggle-share');
                const fileId = button.getAttribute('data-file-id');

                fetch('../includes/toggle_share.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'file_id=' + fileId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const icon = button.querySelector('i');
                            if (data.is_public) {
                                button.classList.remove('btn-secondary');
                                button.classList.add('btn-success');
                                icon.classList.remove('fa-lock');
                                icon.classList.add('fa-lock-open');
                                button.title = 'Public';
                            } else {
                                button.classList.remove('btn-success');
                                button.classList.add('btn-secondary');
                                icon.classList.remove('fa-lock-open');
                                icon.classList.add('fa-lock');
                                button.title = 'Privé';
                            }
                        }
                    });
            }
        });

        // Feedback visuel pendant l'upload
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function () {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> En cours...';
            });
        }
    });
    //anime checkbox private/public
    document.querySelectorAll('.lock-toggle').forEach(toggle => {
        toggle.addEventListener('click', function () {
            const checkbox = this.querySelector('input');
            checkbox.checked = !checkbox.checked;
            const event = new Event('change');
            checkbox.dispatchEvent(event);
        });
    });

    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true,
        'disableScrolling': true
    });
</script>
<script src="../assets/js/script.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<?php require_once '../includes/footer.php'; ?>