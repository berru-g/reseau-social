<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$user = getUserById($_SESSION['user_id']);
$searchResults = [];
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Requête pour récupérer tous les fichiers publics + ceux de l'utilisateur
$query = "
    SELECT uf.*, u.email as owner_email 
    FROM user_files uf
    JOIN users u ON uf.user_id = u.id
    WHERE (uf.is_public = TRUE OR uf.user_id = :user_id)
";

// Si recherche, on ajoute le filtre
if (!empty($searchQuery)) {
    $query .= " AND (uf.file_name LIKE :query OR u.email LIKE :query)";
}

$query .= " ORDER BY uf.upload_date DESC";

$stmt = $pdo->prepare($query);
$params = ['user_id' => $_SESSION['user_id']];

if (!empty($searchQuery)) {
    $params['query'] = '%' . $searchQuery . '%';
}

$stmt->execute($params);
$searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>
<style>
    /* Styles pour les prévisualisations d'images */
    .file-preview {
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }

    .file-preview img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .file-preview:hover img {
        transform: scale(1.03);
    }

    /* Pour les file-card contenant des images */
    .file-card.has-image .file-info {
        padding: 15px;
    }

    /* Pour une meilleure apparence des miniatures */
    .img-thumbnail {
        padding: 0;
        border: none;
        border-radius: 0;
        background-color: transparent;
    }
</style>

<div class="container">
    <h2>Explorer les fichiers partagés</h2>

    <div class="search-container mb-4">
        <form method="get" action="search.php" id="search-form">
            <div class="input-group">
                <input type="text" id="file-search" name="q" class="form-control"
                    placeholder="Rechercher par nom de fichier ou email propriétaire..."
                    value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="search.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="search-results">
        <?php if (!empty($searchQuery)): ?>
            <h4>Résultats pour "<?= htmlspecialchars($searchQuery) ?>" (<?= count($searchResults) ?> fichiers)</h4>
        <?php else: ?>
            <h4>Tous les fichiers publics (<?= count($searchResults) ?>)</h4>
        <?php endif; ?>

        <?php if (empty($searchResults)): ?>
            <div class="alert alert-info">
                <?= empty($searchQuery) ? 'Aucun fichier public disponible.' : 'Aucun fichier trouvé.' ?>
            </div>
        <?php else: ?>
            <!--<div class="file-gallery">
                <?php foreach ($searchResults as $file): ?>
                    <div class="file-card">
                        <div class="file-icon">
                            <?php switch (strtolower($file['file_type'])):
                                case 'csv': ?>
                                    <i class="fas fa-file-csv"></i>
                                    <?php break; ?>
                                <?php case 'xlsx':
                                case 'xls': ?>
                                    <i class="fas fa-file-excel"></i>
                                    <?php break; ?>
                                <?php case 'json': ?>
                                    <i class="fas fa-file-code"></i>
                                    <?php break; ?>
                                <?php default: ?>
                                    <i class="fas fa-file"></i>
                            <?php endswitch; ?>
                        </div>
                        <div class="file-info">
                            <h5><?= htmlspecialchars($file['file_name']) ?></h5>
                            <small>Propriétaire:
                                <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= (int) $file['user_id'] ?>"
                                    class="owner-link">
                                    <?= htmlspecialchars($file['owner_email']) ?>
                                </a>
                            </small>
                            <small><?= date('d/m/Y H:i', strtotime($file['upload_date'])) ?></small>
                            <small class="file-type <?= strtolower($file['file_type']) ?>">
                                <?= strtoupper($file['file_type']) ?>
                            </small>
                            <?php if (!$file['is_public']): ?>
                                <span class="badge badge-warning">Privé</span>
                            <?php endif; ?>
                        </div>
                        <div class="file-actions">
                            <a href="view_chart.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-info" title="Visualiser">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <a href="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>" download
                                class="btn btn-sm btn-success" title="Télécharger">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>-->
            <div class="file-gallery">
                <?php foreach ($searchResults as $file): ?>
                    <div class="file-card">
                        <?php if (strtolower($file['file_type']) === 'image'): ?>
                            <!-- Affiche l'image avec un aperçu -->
                            <div class="file-preview">
                                <a href="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>"
                                    data-lightbox="gallery-<?= $file['id'] ?>"
                                    data-title="<?= htmlspecialchars($file['file_name']) ?>">
                                    <img src="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>"
                                        alt="<?= htmlspecialchars($file['file_name']) ?>" class="img-thumbnail">
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Garde les icônes pour les autres types de fichiers -->
                            <div class="file-icon">
                                <?php switch (strtolower($file['file_type'])):
                                    case 'csv': ?>
                                        <i class="fas fa-file-csv"></i>
                                        <?php break; ?>
                                    <?php case 'xlsx':
                                    case 'xls': ?>
                                        <i class="fas fa-file-excel"></i>
                                        <?php break; ?>
                                    <?php case 'json': ?>
                                        <i class="fas fa-file-code"></i>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <i class="fas fa-file"></i>
                                <?php endswitch; ?>
                            </div>
                        <?php endif; ?>

                        <div class="file-info">
                            <h5><?= htmlspecialchars($file['file_name']) ?></h5>
                            <small>Propriétaire:
                                <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= (int) $file['user_id'] ?>"
                                    class="owner-link">
                                    <?= htmlspecialchars($file['owner_email']) ?>
                                </a>
                            </small>
                            <small><?= date('d/m/Y H:i', strtotime($file['upload_date'])) ?></small>
                            <small class="file-type <?= strtolower($file['file_type']) ?>">
                                <?= strtoupper($file['file_type']) ?>
                            </small>
                            <?php if (!$file['is_public']): ?>
                                <span class="badge badge-warning">Privé</span>
                            <?php endif; ?>
                        </div>

                        <div class="file-actions">
                            <?php if (strtolower($file['file_type']) !== 'image'): ?>
                                <a href="view_chart.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-info" title="Visualiser">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= str_replace('../', BASE_URL . '/', $file['file_path']) ?>" download
                                class="btn btn-sm btn-success" title="Télécharger">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>

    // Configuration du lightbox pour la galerie
    lightbox.option({
        'albumLabel': 'Image %1 sur %2',
        'wrapAround': true,
        'fadeDuration': 200,
        'imageFadeDuration': 200,
        'resizeDuration': 200
    });


    $(document).ready(function () {
        // Autocomplétion
        $("#file-search").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "../includes/search_autocomplete.php",
                    dataType: "json",
                    data: { term: request.term },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $("#search-form").submit();
            }
        });

        // Filtrage en temps réel (optionnel)
        $("#file-search").on("input", function () {
            const query = $(this).val().toLowerCase();

            $(".file-card").each(function () {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
        });
    });


</script>

<?php require_once '../includes/footer.php'; ?>