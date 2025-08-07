<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$user = getUserById($_SESSION['user_id']);
/* Un seul accé autorisé ...
if ($user['username'] !== 'berru' || $user['email'] !== 'g.leberruyer@gmail.com') {
    http_response_code(403);
    exit("⛔ Accès interdit.");
}*/
// redirection
if ($user['username'] !== 'berru' || $user['email'] !== 'g.leberruyer@gmail.com') {
    header("Location: " . BASE_URL . "/pages/profile.php");
    exit;
}


// Récupération des stats globales
$stats = [];
// Récupérer le top des utilisateurs actifs
$top_active_users = getTopActiveUsers($pdo, 5);
// Utilisateurs
$stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['new_users_last_30'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 30 DAY")->fetchColumn();

// Fichiers
$stats['total_files'] = $pdo->query("SELECT COUNT(*) FROM user_files")->fetchColumn();
$stats['public_files'] = $pdo->query("SELECT COUNT(*) FROM user_files WHERE is_public = 1")->fetchColumn();
$stats['file_types'] = $pdo->query("SELECT file_type, COUNT(*) as count FROM user_files GROUP BY file_type")->fetchAll(PDO::FETCH_ASSOC);

// Activités
$stats['comments_count'] = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$stats['likes_count'] = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();

// Top utilisateurs
$stats['top_uploaders'] = $pdo->query("SELECT u.username, COUNT(f.id) as uploads FROM users u JOIN user_files f ON u.id = f.user_id GROUP BY u.id ORDER BY uploads DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$stats['top_commented'] = $pdo->query("SELECT u.username, COUNT(c.id) as comments FROM users u JOIN comments c ON u.id = c.user_id GROUP BY u.id ORDER BY comments DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// stat img
$stats['image_extensions'] = $pdo->query("
    SELECT 
        CASE 
            WHEN file_type = 'jpg' THEN 'JPG'
            WHEN file_type = 'pdf' THEN 'PDF'
            WHEN file_type = 'png' THEN 'PNG'
            WHEN file_type = 'mp4' THEN 'MP4'
            ELSE 'Autres'
        END AS extension,
        COUNT(*) as count
    FROM user_files_img
    GROUP BY extension
")->fetchAll(PDO::FETCH_ASSOC);

// RANK - Version corrigée et sécurisée
$current_user_id = $_SESSION['user_id'];

try {
    $rank_query = "
        SELECT 
            u.id, 
            u.username,
            u.profile_picture,
            (SELECT COUNT(*) FROM user_files WHERE user_id = u.id) as uploads,
            (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comments_count,
            (SELECT COUNT(*) FROM likes WHERE user_id = u.id) as likes_count,
            ((SELECT COUNT(*) FROM user_files WHERE user_id = u.id) * 10) + 
            ((SELECT COUNT(*) FROM comments WHERE user_id = u.id) * 5) + 
            ((SELECT COUNT(*) FROM likes WHERE user_id = u.id) * 3) as xp
        FROM users u
        ORDER BY xp DESC
        LIMIT 5
    ";

    $stmt = $pdo->query($rank_query);
    $top_active_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($top_active_users as &$rank_user) {
        $rank_user['is_current'] = ($rank_user['id'] == $current_user_id);
        $level_info = calculateUserLevel($rank_user['xp']);
        $rank_user['level'] = $level_info['level'];
        $rank_user['next_level_xp'] = $level_info['next_level_xp'];
        $rank_user['xp_percentage'] = $level_info['xp_percentage'];
    }
} catch (PDOException $e) {
    error_log("Erreur RANK: " . $e->getMessage());
    $top_active_users = []; // Retourne un tableau vide en cas d'erreur
}

// Charger la liste de mots interdits
// Charger la liste de mots interdits
$badWordsFile = __DIR__ . '/../lang/badwords.json';
$badWords = json_decode(file_get_contents($badWordsFile), true);
$lang = 'fr';
$words = $badWords[$lang] ?? $badWords['fr'];
$pattern = '/' . implode('|', array_map('preg_quote', $words)) . '/i';

// Récupérer tous les commentaires récents
$sql = "
    SELECT c.id, c.content, c.created_at, c.file_path, 
           u.id as user_id, u.username, u.email, u.profile_picture
    FROM comments c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
    LIMIT 1000
";
$allComments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Filtrer en PHP
$suspectComments = array_filter($allComments, function ($comment) use ($pattern) {
    return preg_match($pattern, $comment['content']);
});

require_once '../includes/header.php';
?>
<div class="container">
    <h2>📊 Statistiques Globales</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><i class="fas fa-users"></i> Utilisateurs</h3>
            <div id="usersChart" style="width: 100%; height: 200px;"></div>
            <p>Total : <?= $stats['total_users'] ?></p>
            <p>Nouveaux (30j) : <?= $stats['new_users_last_30'] ?></p>
        </div>

        <div class="stat-card">
            <h3><i class="fas fa-file-upload"></i> Fichiers</h3>
            <div id="filesChart" style="width: 100%; height: 200px;"></div>
            <p>Total : <?= $stats['total_files'] ?></p>
            <p>Publics : <?= $stats['public_files'] ?></p>
        </div>

        <div class="stat-card">
            <h3><i class="fas fa-image"></i> Types d'images</h3>
            <div id="imagesChart" style="width: 100%; height: 200px;"></div>
            <ul>
                <?php foreach ($stats['image_extensions'] as $ext): ?>
                    <li><?= htmlspecialchars($ext['extension']) ?> : <?= $ext['count'] ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="stat-card">
            <h3><i class="fas fa-comments"></i> Interactions</h3>
            <div id="interactionsChart" style="width: 100%; height: 200px;"></div>
            <p>Commentaires : <?= $stats['comments_count'] ?></p>
            <p>Likes : <?= $stats['likes_count'] ?></p>
        </div>

        <div class="stat-card">
            <h3><i class="fas fa-trophy"></i> Top Uploaders</h3>
            <div id="uploadersChart" style="width: 100%; height: 200px;"></div>
        </div>

        <div class="stat-card">
            <h3><i class="fas fa-fire"></i> Plus actifs (posts)</h3>
            <div id="activeUsersChart" style="width: 100%; height: 200px;"></div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2>🏆 Classement des Utilisateurs</h2>

    <div class="user-ranking">
        <?php foreach ($top_active_users as $user): ?>
            <div class="user-card <?= $rank_user['is_current'] ? 'current-user' : '' ?>">
                <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= (int) $user['id'] ?>" class="user-avatar-link">
                    <div class="user-avatar">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_picture']) ?>?<?= time() ?>" alt="Photo de profil"
                                class="profile-picture-thumbnail">
                        <?php else: ?>
                            <div class="default-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                        <?php endif; ?>
                        <div class="user-level" title="Niveau <?= $rank_user['level'] ?>">
                            <?= getLevelBadge($rank_user['level']) ?>
                        </div>
                    </div>
                </a>
                <div class="user-info">
                    <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= (int) $user['id'] ?>" class="user-name-link">
                        <h4><?= htmlspecialchars($user['username']) ?></h4>
                    </a>
                    <div class="user-stats">
                        <span><i class="fas fa-file-upload"></i> <?= $user['uploads'] ?></span>
                        <span><i class="fas fa-comment"></i> <?= $user['comments'] ?></span>
                        <span><i class="fas fa-heart"></i> <?= $user['likes'] ?></span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $user['xp_percentage'] ?>%"></div>
                        <span>XP: <?= $user['xp'] ?>/<?= $user['next_level_xp'] ?></span>
                    </div>
                </div>
                <!--<span><i class="fas fa-comment"></i> <?= $rank_user['comments_count'] ?></span>
                <span><i class="fas fa-heart"></i> <?= $rank_user['likes_count'] ?></span>-->
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Styles (conservés identiques) -->
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .stat-card {
        background: #f5f7fa;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card h3 {
        margin-bottom: 1rem;
        font-size: 1.2rem;
        color: #333;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-card p,
    .stat-card li {
        margin: 0.4rem 0;
        color: #555;
        font-size: 0.95rem;
    }

    .stat-card ul {
        padding-left: 1.2rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .user-ranking {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .user-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1.5rem;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .user-card:hover {
        transform: translateY(-5px);
    }

    .user-avatar {
        position: relative;
        width: 70px;
        height: 70px;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #f5f7fa;
    }

    .user-avatar-link {
        display: block;
        text-decoration: none;
        position: relative;
        width: 70px;
        height: 70px;
    }

    .user-name-link {
        text-decoration: none;
        color: #ab9ff2;
    }

    .user-name-link:hover h4 {
        color: #2575fc;
    }

    .user-level {
        position: absolute;
        bottom: -5px;
        right: -5px;
        background: #2575fc;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
        border: 2px solid white;
    }

    .user-info {
        flex: 1;
    }

    .user-info h4 {
        margin: 0 0 0.5rem 0;
        color: #333;
    }

    .user-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .user-stats span {
        color: #666;
    }

    .progress-container {
        background: #f5f7fa;
        border-radius: 20px;
        height: 20px;
        position: relative;
        margin-top: 0.5rem;
    }

    .progress-bar {
        background: linear-gradient(90deg, #ab9ff2, #2575fc);
        border-radius: 20px;
        height: 100%;
    }

    .progress-container span {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.7rem;
        color: white;
        font-weight: bold;
    }

    .suspect-comments {
        max-height: 600px;
        overflow-y: auto;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #eee;
    }

    .suspect-comments .card {
        transition: transform 0.2s;
    }

    .suspect-comments .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

<!-- Scripts amCharts -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<script>
    // Appliquer le thème
    am4core.useTheme(am4themes_animated);

    // Graphique Utilisateurs
    const usersChart = am4core.create("usersChart", am4charts.PieChart);
    usersChart.data = [{
        "category": "Nouveaux (30j)",
        "value": <?= $stats['new_users_last_30'] ?>
    }, {
        "category": "Anciens",
        "value": <?= $stats['total_users'] - $stats['new_users_last_30'] ?>
    }];
    const usersSeries = usersChart.series.push(new am4charts.PieSeries());
    usersSeries.dataFields.value = "value";
    usersSeries.dataFields.category = "category";
    usersSeries.slices.template.stroke = am4core.color("#fff");
    usersSeries.slices.template.strokeWidth = 2;
    usersSeries.colors.list = [
        am4core.color("#ab9ff2"),
        am4core.color("#2575fc")
    ];

    // Graphique Types de fichiers
    const filesChart = am4core.create("filesChart", am4charts.PieChart);
    filesChart.data = [
        <?php foreach ($stats['file_types'] as $type): ?>
                    {
                "category": "<?= ucfirst($type['file_type']) ?>",
                "value": <?= $type['count'] ?>
            },
        <?php endforeach; ?>
    ];
    const filesSeries = filesChart.series.push(new am4charts.PieSeries());
    filesSeries.dataFields.value = "value";
    filesSeries.dataFields.category = "category";
    filesSeries.slices.template.stroke = am4core.color("#fff");
    filesSeries.slices.template.strokeWidth = 2;
    filesSeries.colors.list = [
        am4core.color("#ffd97d"),
        am4core.color("#2575fc"),
        am4core.color("#60d394"),
        am4core.color("#ee6055")
    ];

    // Graphique Interactions
    const interactionsChart = am4core.create("interactionsChart", am4charts.XYChart);
    interactionsChart.data = [{
        "category": "Commentaires",
        "value": <?= $stats['comments_count'] ?>
    }, {
        "category": "Likes",
        "value": <?= $stats['likes_count'] ?>
    }];
    const categoryAxis = interactionsChart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "category";
    categoryAxis.renderer.grid.template.location = 0;
    const valueAxis = interactionsChart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;
    const series = interactionsChart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = "value";
    series.dataFields.categoryX = "category";
    series.columns.template.fillOpacity = .8;
    series.columns.template.stroke = am4core.color("#fff");
    series.columns.template.strokeWidth = 2;
    series.columns.template.adapter.add("fill", function (fill, target) {
        return target.dataItem.index === 0
            ? am4core.color("#ffd97d")
            : am4core.color("#77a9ffff");
    });

    // Graphique Types d'images
    const imagesChart = am4core.create("imagesChart", am4charts.PieChart);
    imagesChart.data = [
        <?php foreach ($stats['image_extensions'] as $ext): ?>
                    {
                "category": "<?= $ext['extension'] ?>",
                "value": <?= $ext['count'] ?>
            },
        <?php endforeach; ?>
    ];
    const imagesSeries = imagesChart.series.push(new am4charts.PieSeries());
    imagesSeries.dataFields.value = "value";
    imagesSeries.dataFields.category = "category";
    imagesSeries.slices.template.stroke = am4core.color("#fff");
    imagesSeries.slices.template.strokeWidth = 2;
    imagesSeries.colors.list = [
        am4core.color("#ffd97d"),
        am4core.color("#ab9ff2"),
        am4core.color("#60d394"),
        am4core.color("#ff7b89")
    ];

    // Graphique Top Uploaders
    const uploadersChart = am4core.create("uploadersChart", am4charts.XYChart);
    uploadersChart.data = [
        <?php foreach ($stats['top_uploaders'] as $user): ?>
                    {
                "name": "<?= htmlspecialchars($user['username']) ?>",
                "uploads": <?= $user['uploads'] ?>
            },
        <?php endforeach; ?>
    ];
    const uploadersCategoryAxis = uploadersChart.xAxes.push(new am4charts.CategoryAxis());
    uploadersCategoryAxis.dataFields.category = "name";
    uploadersCategoryAxis.renderer.grid.template.location = 0;
    const uploadersValueAxis = uploadersChart.yAxes.push(new am4charts.ValueAxis());
    uploadersValueAxis.min = 0;
    const uploadersSeries = uploadersChart.series.push(new am4charts.ColumnSeries());
    uploadersSeries.dataFields.valueY = "uploads";
    uploadersSeries.dataFields.categoryX = "name";
    uploadersSeries.columns.template.fill = am4core.color("#60d394");
    uploadersSeries.columns.template.stroke = am4core.color("#fff");
    uploadersSeries.columns.template.strokeWidth = 2;

    // Graphique Utilisateurs actifs
    const activeUsersChart = am4core.create("activeUsersChart", am4charts.XYChart);
    activeUsersChart.data = [
        <?php foreach ($stats['top_commented'] as $user): ?>
                    {
                "name": "<?= htmlspecialchars($user['username']) ?>",
                "comments": <?= $user['comments'] ?>
            },
        <?php endforeach; ?>
    ];
    const activeCategoryAxis = activeUsersChart.xAxes.push(new am4charts.CategoryAxis());
    activeCategoryAxis.dataFields.category = "name";
    activeCategoryAxis.renderer.grid.template.location = 0;
    const activeValueAxis = activeUsersChart.yAxes.push(new am4charts.ValueAxis());
    activeValueAxis.min = 0;
    const activeSeries = activeUsersChart.series.push(new am4charts.ColumnSeries());
    activeSeries.dataFields.valueY = "comments";
    activeSeries.dataFields.categoryX = "name";
    activeSeries.columns.template.fill = am4core.color("#ff7b89");
    activeSeries.columns.template.stroke = am4core.color("#fff");
    activeSeries.columns.template.strokeWidth = 2;

    // Désinitialisation propre quand on quitte la page
    window.addEventListener('beforeunload', function () {
        [usersChart, filesChart, interactionsChart, imagesChart, uploadersChart, activeUsersChart].forEach(chart => {
            if (chart) {
                chart.dispose();
            }
        });
    });
</script>

<!-- le coin du modérateur -- promis on automatisera ça à l'avenir -->
<div class="container mt-5">
    <h2>🚩 le coin du modérateur</h2>


    <?php if (empty($suspectComments)): ?>
        <div class="alert alert-success">Aucun contenu suspect détecté.</div>
    <?php else: ?>
        <div class="alert alert-danger">
            <?= count($suspectComments) ?> commentaire(s) suspect(s) détecté(s)
        </div>

        <div class="suspect-comments">
            <?php foreach ($suspectComments as $comment): ?>
                <div class="card mb-3 border-danger">
                    <div class="card-header bg-danger text-white">
                        <div class="d-flex justify-content-between">
                            <div>
                                <img src="<?= htmlspecialchars($comment['profile_picture'] ?? '') ?>" alt="Photo de profil"
                                    class="rounded-circle me-2" width="30" height="30">
                                <strong><?= htmlspecialchars($comment['username']) ?></strong>
                                (<?= htmlspecialchars($comment['email']) ?>)
                            </div>
                            <small>
                                <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>

                        <?php if (!empty($comment['file_path'])): ?>
                            <?php if (strpos($comment['file_path'], '.jpg') !== false || strpos($comment['file_path'], '.png') !== false): ?>
                                <img src="<?= htmlspecialchars($comment['file_path']) ?>" class="img-fluid rounded mt-2"
                                    style="max-height: 200px;">
                            <?php elseif (strpos($comment['file_path'], '.mp4') !== false): ?>
                                <video controls class="mt-2" style="max-width: 300px;">
                                    <source src="<?= htmlspecialchars($comment['file_path']) ?>" type="video/mp4">
                                </video>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light">
                        <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= (int) $comment['user_id'] ?>"
                            class="btn btn-sm btn-outline-primary">
                            Voir profil
                        </a>
                        <button class="btn btn-sm btn-outline-danger float-end">
                            Supprimer
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



<?php require_once '../includes/footer.php'; ?>