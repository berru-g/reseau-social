<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

// Identifiants
$userId = $_SESSION['user_id']; // l'utilisateur connecté
$user = getUserById($userId);   // ses infos

$profileUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $userId;
$profileUser = getUserById($profileUserId);

// Traitement uniquement si l'utilisateur modifie son propre profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId === $profileUserId) {
    // Mise à jour du site web
    if (isset($_POST['website_url'])) {
        $websiteUrl = filter_var($_POST['website_url'], FILTER_SANITIZE_URL);

        $stmt = $pdo->prepare("UPDATE users SET website_url = ? WHERE id = ?");
        $stmt->execute([$websiteUrl, $userId]);
        $user['website_url'] = $websiteUrl;
    }

    // Upload de la photo de profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profile_pictures/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExt = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileExt), $allowedTypes)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }

                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$targetPath, $userId]);
                $user['profile_picture'] = $targetPath;
            }
        }
    }
}

// Statistiques du profil visité
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_files WHERE user_id = ?");
$stmt->execute([$profileUserId]);
$fileCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_files WHERE user_id = ? AND is_public = 1");
$stmt->execute([$profileUserId]);
$publicFileCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_files_img WHERE user_id = ?");
$stmt->execute([$profileUserId]);
$imageCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
$stmt->execute([$profileUserId]);
$commentCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM likes 
    WHERE comment_id IN (SELECT id FROM comments WHERE user_id = ?)
");
$stmt->execute([$profileUserId]);
$likesReceived = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM user_files WHERE user_id = ? ORDER BY upload_date DESC");
$stmt->execute([$profileUserId]);
$userFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="profile-app">
    <div class="profile-header">
        <div class="profile-avatar-section">
            <div class="avatar-edit">
                <img src="<?= !empty($profileUser['profile_picture']) ? htmlspecialchars($profileUser['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($profileUser['username']) . '&background=ab9ff2&color=fff' ?>"
                    alt="Avatar de <?= htmlspecialchars($profileUser['username']) ?>" class="avatar-image">

                <?php if ($userId === $profileUserId): ?>
                    <form method="post" enctype="multipart/form-data" class="avatar-form">
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" hidden>
                        <button type="button" class="btn-primary"
                            onclick="document.getElementById('profile_picture').click()">
                            <i class="fas fa-camera"></i> Changer
                        </button>
                        <button type="submit" class="btn-ghost" id="submit-btn" style="display:none;">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="profile-identity">
                <h1 class="profile-title"><?= htmlspecialchars($profileUser['username']) ?></h1>

                <?php if (!empty($profileUser['website_url'])): ?>
                    <div class="website-display">
                        <a href="<?= htmlspecialchars($profileUser['website_url']) ?>" target="_blank" class="website-link">
                            <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($profileUser['website_url']) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="profile-meta">
                    <span class="meta-item">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($profileUser['email']) ?>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-calendar-alt"></i> Membre depuis le
                        <?= date('d/m/Y', strtotime($profileUser['created_at'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('profile_picture')?.addEventListener('change', function () {
            if (this.files.length > 0) {
                document.getElementById('submit-btn').style.display = 'inline-block';
            }
        });
    </script>

    <h3 class="section-title"><i class="fas fa-chart-pie"></i> Dashboard</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-purple"><i class="fas fa-image"></i></div>
            <div class="stat-info"><span class="stat-count"><?= $imageCount ?></span><span
                    class="stat-label">Images</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fas fa-file-upload"></i></div>
            <div class="stat-info"><span class="stat-count"><?= $fileCount ?></span><span
                    class="stat-label">Fichiers</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fas fa-globe"></i></div>
            <div class="stat-info"><span class="stat-count"><?= $publicFileCount ?></span><span
                    class="stat-label">Publics</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-orange"><i class="fas fa-comment"></i></div>
            <div class="stat-info"><span class="stat-count"><?= $commentCount ?></span><span
                    class="stat-label">Commentaires</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-red"><i class="fas fa-heart"></i></div>
            <div class="stat-info"><span class="stat-count"><?= $likesReceived ?></span><span class="stat-label">Likes
                    reçus</span></div>
        </div>
        <!-- modif : if ($user['username'] !== 'name de user autorisé' || $user['email'] !== 'maildeuserAuto@gmail.com')
            || est toujours vrai dès qu’un seul des deux n’est pas bon, pour vérifier que c’est bien moi et uniquement moi alors utiliser, && -->
        <?php if (
            isset($user['username'], $user['email']) &&
            $user['username'] === 'admin' &&
            $user['email'] === 'contact@gael-berru.com'
        ): ?>
            <div class="stat-card clickable" onclick="location.href='<?= BASE_URL ?>/pages/mon-dashboard.php'">
                <div class="stat-icon bg-gradient"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Modération</span>
                    <span class="stat-link">& statistiques <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <h3 class="section-title"><i class="fas fa-file"></i> Fichier Public</h3>
    <div class="stats-grid">
        <?php foreach ($userFiles as $file): ?>
            <div class="file-card" style="max-width:200px;display:flex;">
                <div class="file-icon">
                    <i
                        class="fas fa-file<?= $file['file_type'] === 'csv' ? '-csv' : ($file['file_type'] === 'excel' ? '-excel' : ($file['file_type'] === 'json' ? '-code' : '')) ?>"></i>
                </div>
                <!-- à adapter comme gallery et search -->
                <div class="file-info">
                    <h5><?= htmlspecialchars($file['file_name']) ?></h5>
                    <small><?= date('d/m/Y H:i', strtotime($file['upload_date'])) ?></small>
                    <small class="file-type"><?= strtoupper($file['file_type']) ?></small>
                </div>
                <div class="file-actions">
                    <a href="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>" download
                        class="btn btn-sm btn-success">
                        <i class="fas fa-download"></i>
                    </a>
                    <a href="view_chart.php?id=<?= $file['id'] ?>" class="btn btn-info">
                        <i class="fas fa-chart-line"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($userId === $profileUserId): ?>
        <div class="profile-edit-section">
            <h3 class="section-title"><i class="fas fa-user-cog"></i> Personnalisation</h3>
            <form method="post" class="website-form">
                <div class="form-group">
                    <label for="website_url">Votre site web :</label>
                    <div class="input-group">
                        <input type="url" name="website_url" id="website_url"
                            value="<?= htmlspecialchars($user['website_url'] ?? '') ?>" placeholder="https://example.com">
                        <button type="submit" class="btn-primary">Mettre à jour</button>
                    </div>
                </div>
            </form>
            <div class="action-buttons">
                <a href="change-password.php" class="btn-primary"><i class="fas fa-key"></i> Changer le mot de passe</a>
                <?php if (
                    isset($user['username'], $user['email']) &&
                    $user['username'] === 'admin' &&
                    $user['email'] === 'contact@gael-berru.com'
                ): ?>
                    <a href="<?= BASE_URL ?>/wallet/wallet.php" class="btn-ghost"><i class="fas fa-chart-pie"></i> Wallet all in
                        one</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


<style>
    .profile-app {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }

    .profile-header {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .profile-avatar-section {
        display: flex;
        align-items: flex-start;
        gap: 2rem;
    }

    .avatar-edit {
        text-align: center;
        flex-shrink: 0;
    }

    .avatar-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f0f0f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    .avatar-form {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .profile-identity {
        flex: 1;
    }

    .profile-title {
        margin: 0 0 0.5rem 0;
        font-size: 1.8rem;
        color: #333;
        font-weight: 600;
    }

    .profile-meta {
        margin-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        color: #666;
        font-size: 0.95rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .website-display {
        margin: 0.5rem 0;
    }

    .website-display a {
        color: #4d8af0;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        word-break: break-all;
    }

    .website-display a:hover {
        text-decoration: underline;
    }

    .profile-edit-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .section-title {
        color: #555;
        font-size: 1.2rem;
        margin: 0 0 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .profile-avatar-section {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .avatar-form {
            flex-direction: row;
            justify-content: center;
        }
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 1.2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
    }

    .stat-card.clickable {
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .bg-purple {
        background: #ab9ff2;
    }

    .bg-blue {
        background: #4d8af0;
    }

    .bg-green {
        background: #3bb873;
    }

    .bg-orange {
        background: #ff914d;
    }

    .bg-red {
        background: #ff5a5f;
    }

    .bg-gradient {
        background: linear-gradient(135deg, #ab9ff2, #4d8af0);
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-count {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-link {
        font-size: 0.8rem;
        color: #ab9ff2;
        margin-top: 0.2rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    /* Boutons modernes */
    .btn-primary {
        background: #ab9ff2;
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        background: #8a7bd9;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(171, 159, 242, 0.3);
    }

    .btn-ghost {
        background: transparent;
        color: #ab9ff2;
        border: 1px solid #ab9ff2;
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-ghost:hover {
        background: rgba(171, 159, 242, 0.1);
    }

    .input-group {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        border: 1px solid #e0e0e0;
    }

    .input-icon {
        color: #666;
        margin-right: 0.5rem;
    }

    .avatar-form {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .website-display {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .website-link {
        color: #4d8af0;
        text-decoration: none;
        word-break: break-all;
    }

    .website-link:hover {
        text-decoration: underline;
    }

    .profile-edit-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }

    .profile-edit-section h3 {
        color: #555;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #555;
    }

    .input-group input {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .profile-avatar-section {
            flex-direction: column;
            text-align: center;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>