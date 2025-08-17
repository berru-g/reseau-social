<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$userId = $_SESSION['user_id']; // l'utilisateur connecté
$user = getUserById($userId);   // ses infos

$profileUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $userId;
$profileUser = getUserById($profileUserId);
//$owner = getUserById($file['user_id']); // affcihe img otheruser - ajouter sql
//$comments = getAllComments();
$comments = getParentComments(); // pour afficher le com sous un post ciblé ? 1h pour trouver ce bug gael vas te coucher (ouai je me parle tout seul putain je suis fou ça y'est. ..)
//$userLiked = hasUserLiked($comment['id'], $user['id']);  //bug ici

// Traitement du formulaire de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $content = trim($_POST['content']);
    $file_path = null;
    $file_type = null;

    if (!empty($_FILES['file']['name'])) {
        $upload = uploadFile($_FILES['file']);
        if (!isset($upload['error'])) {
            $file_path = $upload['path'];
            $file_type = $upload['type'];
        }
    }

    if (!empty($content) || $file_path) {
        addComment($_SESSION['user_id'], $content, null, $file_path, $file_type);
        header("Location: " . BASE_URL);
        exit;
    }
}

// Traitement des likes
if (isset($_GET['like'])) {
    $comment_id = intval($_GET['like']);
    likeComment($_SESSION['user_id'], $comment_id);
    header("Location: " . BASE_URL);
    exit;
}

// Traitement du formulaire de réponse sous un post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply']) && isset($_POST['parent_id'])) {
    $content = trim($_POST['content']);
    $file_path = null;
    $file_type = null;

    if (!empty($_FILES['file']['name'])) {
        $upload = uploadFile($_FILES['file']);
        if (!isset($upload['error'])) {
            $file_path = $upload['path'];
            $file_type = $upload['type'];
        }
    }

    if (!empty($content) || $file_path) {
        $parent_id = intval($_POST['parent_id']);
        addComment($_SESSION['user_id'], $content, $parent_id, $file_path, $file_type);
        header("Location: " . BASE_URL);
        exit;
    }
}

require_once '../includes/header.php';
?>


<div class="container" id="mur">
    <!--<div class="comment-form">
        <h2>Salut <?= htmlspecialchars($user['username']) ?></h2>
        <form method="POST" enctype="multipart/form-data" class="reply-form">
            <textarea name="content" placeholder="Exprimez-vous "></textarea>

            <label for="file-main" class="file-label">
                <i class="fas fas fa-image"></i> Parcourir
            </label>
            <input type="file" name="file" id="file-main" accept="image/*,video/*">

            <button type="submit" name="comment" class="btn-reply">
                <i class="fas fa-paper-plane"></i> Poster
            </button>
        </form>
    </div>-->

    <div class="comments">
        <h2>Social Feed</h2>

        <?php foreach ($comments as $comment): ?>

            <div class="comment" data-aos="fade-up" data-aos-delay="100">

                <!--(int) pour éviter les injections SQL et htmlspecialchars pour protéger contre les XSS --Normalement -->
                <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= (int) $comment['user_id'] ?>"
                    class="profile-picture-link">
                    <?php if (!empty($comment['profile_picture'])): ?>
                        <img src="<?= htmlspecialchars($comment['profile_picture']) ?>?<?= time() ?>" alt="Photo de profil"
                            class="profile-picture-thumbnail">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </a>

                <span class="comment-date">
                    <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                </span>

                <p><strong><?= htmlspecialchars($comment['username']) ?></strong> :</p>

                <?php if (!empty($comment['content'])): ?>
                    <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                <?php endif; ?>
                <!--Ajouter l'envoie à la table user_xp , pour afficher le rank -->
                <?php if (!empty($comment['file_path'])): ?>
                    <!--ajouter lightbox comme pour search-->
                    <?php if ($comment['file_type'] === 'image'): ?>
                        <a href="<?= htmlspecialchars($comment['file_path']) ?>" data-lightbox="comment-gallery"
                            data-title="Image partagée">
                            <img src="<?= htmlspecialchars($comment['file_path']) ?>" alt="image partagée" style="max-width:200px;">
                        </a>
                    <?php elseif ($comment['file_type'] === 'video'): ?>
                        <video controls style="max-width:200px;">
                            <source src="<?= htmlspecialchars($comment['file_path']) ?>" type="video/mp4">
                            Votre navigateur ne supporte pas la vidéo.
                        </video>
                    <?php endif; ?>

                <?php endif; ?>


                <div class="comment-actions">

                    <a href="?like=<?= $comment['id'] ?>" class="like-btn <?= $userLiked ? 'liked' : 'not-liked' ?>"
                        id="likeBtn">
                        <i class="fas fa-heart"></i> <?= $comment['like_count'] ?>
                    </a>

                </div>
            </div>

            <!-- Formulaire de réponse -->
            <form method="POST" enctype="multipart/form-data" class="reply-form" data-aos="fade-up" data-aos-delay="200">
                <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                <textarea name="content" placeholder="Répondre à ce post..."></textarea>

                <label for="file-<?= $comment['id'] ?>" class="file-label">
                    <i class="fas fas fa-image"></i> Parcourir
                </label>
                <input type="file" name="file" id="file-<?= $comment['id'] ?>" accept="image/*,video/*">

                <button type="submit" name="reply" class="btn-reply">
                    <i class="fas fa-reply"></i> Répondre
                </button>
            </form>

            <!-- Affichage des réponses -->
            <?php foreach (getReplies($comment['id']) as $reply): ?>
                <div class="reply" style="margin-left: 40px; border-left: 2px solid #ccc; padding-left: 10px;"
                    data-aos="fade-up" data-aos-delay="300">
                    <!--<?php if (!empty($reply['profile_picture'])): ?>
                        <img src="<?= htmlspecialchars($reply['profile_picture']) ?>?<?= time() ?>"
                            class="profile-picture-thumbnail">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>-->

                    <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= (int) $reply['user_id'] ?>"
                        class="profile-picture-link">
                        <?php if (!empty($reply['profile_picture'])): ?>
                            <img src="<?= htmlspecialchars($reply['profile_picture']) ?>?<?= time() ?>"
                                class="profile-picture-thumbnail">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </a>

                    <p><strong><?= htmlspecialchars($reply['username']) ?></strong> a répondu :</p>

                    <?php if (!empty($reply['content'])): ?>
                        <p><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($reply['file_path'])): ?>
                        <?php if ($reply['file_type'] === 'image'): ?>
                            <img src="<?= htmlspecialchars($reply['file_path']) ?>" style="max-width:200px;" alt="image réponse">
                        <?php elseif ($reply['file_type'] === 'video'): ?>
                            <video controls style="max-width:200px;">
                                <source src="<?= htmlspecialchars($reply['file_path']) ?>" type="video/mp4">
                            </video>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php endforeach; ?>
    </div>
    <style>
        /*test input en bas */
        .comment-post {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: transparent;
            padding: 10px 15px;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
            border-top: 1px solid #ddd;
            z-index: 9999;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .reply-post {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reply-post textarea {
            flex: 1;
            resize: none;
            height: 50px;
            padding: 10px 10px;
            font-size: 10px;
            font-family: inherit;
            border-radius: 24px;
            border: 1px solid #ccc;
            background: #f9f9f9;
        }

        .file-post,
        .btn-post {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .file-post:hover,
        .btn-post:hover {
            background: var(--accent-color);
        }

        .file-post input {
            display: none;
        }
    </style>
    <!--nouveau form fixé en bas -->
    <div class="comment-post">
        <form method="POST" enctype="multipart/form-data" class="reply-post">
            <textarea name="content" placeholder="Exprimez-vous"></textarea>

            <!-- Bouton Parcourir (icone image) -->
            <label for="file-main" class="file-post" title="Parcourir">
                <i class="fas fa-image"></i><input type="file" name="file" id="file-main" accept="image/*,video/*">
            </label>


            <!-- Bouton Envoyer (icone avion) -->
            <button type="submit" name="comment" class="btn-post" title="Poster">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

</div>
<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<!-- JS AOS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,        // Durée de l'animation en ms
        easing: 'ease-out',   // Type d'effet
        once: true,           // Ne joue l'animation qu'une fois
    });


    // Configuration du lightbox pour la galerie
    lightbox.option({
        'albumLabel': 'Image %1 sur %2',
        'wrapAround': true,
        'fadeDuration': 200,
        'imageFadeDuration': 200,
        'resizeDuration': 200
    });

</script>

<?php require_once '../includes/footer.php'; ?>