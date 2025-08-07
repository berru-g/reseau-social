<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

?><!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SITE_NAME ?></title>
  
  <link rel="icon" href="<?= BASE_URL ?>/assets/img/agora-logo.png" type="image/x-icon">
  <meta name="description"
    content="Agora Social Feed est une plateforme tout-en-un de partage de fichiers privés ou publics, avec visualisation et export de données (CSV, JSON, Excel, PDF, PNG) et messagerie communautaire.">
  <meta name="keywords"
    content="partage fichier, csv to pdf, data visualizer, excel, json, export, plateforme collaboratif, outil data, mur de partage, messagerie collaborative">
  <meta name="robots" content="index, follow">
  <meta name="author" content="Agora Social Feed">

  <!-- Open Graph pour Facebook / LinkedIn -->
  <meta property="og:title" content="Agora Social Feed – Partage intelligent de fichiers et données">
  <meta property="og:description"
    content="Réseau social minimaliste. Partagez, visualisez et exportez vos fichiers CSV, Excel, JSON et PDF. Outil collaboratif tout-en-un.">
  <meta property="og:image" content="<?= BASE_URL ?>/assets/img/agora-logo.png">
  <meta property="og:url" content="https://example.com">
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Agora Social Feed – Partage et visualisation de fichiers CSV/Excel">
  <meta name="twitter:description"
    content="Transformez vos données avec Agora Social Feed. Partagez, visualisez et exportez tous types de fichiers.">
  <meta name="twitter:image" content="<?= BASE_URL ?>/assets/img/agora-logo.png">
  <!-- Canonical URL -->
  <link rel="canonical" href="https://example.com">

  <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> revoir tout le css bootstrap c'est vrt de la grosse merde-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
  <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>-->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // Fail-safe si jQuery ne charge pas
    window.jQuery || document.write('<script src="<?= BASE_URL ?>/assets/js/jquery.min.js">\x3C/script>');
  </script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/fontawesome.min.css" rel="stylesheet">

  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Agora Social Feed",
  "url": "https://example.com",
  "description": "Plateforme de partage et de visualisation de fichiers CSV, JSON, Excel, avec export PDF/PNG et messagerie collaborative.",
  "applicationCategory": "BusinessApplication",
  "browserRequirements": "Requires HTML5",
  "operatingSystem": "All",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "EUR"
  }
}
</script>

  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "HowTo",
  "name": "Comment utiliser Agora Social Feed pour transformer un fichier CSV en graphique PDF ?",
  "step": [
    {
      "@type": "HowToStep",
      "text": "Connectez-vous à votre compte Agora Social Feed."
    },
    {
      "@type": "HowToStep",
      "text": "Importez votre fichier CSV ou Excel via l'interface drag & drop."
    },
    {
      "@type": "HowToStep",
      "text": "Sélectionnez les colonnes à visualiser et le type de graphique."
    },
    {
      "@type": "HowToStep",
      "text": "Exportez le graphique en PDF ou PNG."
    }
  ],
  "tool": [
    {
      "@type": "HowToTool",
      "name": "Module Data Visualizer"
    }
  ],
  "totalTime": "PT1M",
  "image": "https://example.com/assets/howto-preview.png"
}
</script>

</head>

<body>
  <header class="fixed-header">
    <div class="header-content">
      <div class="profile-dropdown">
        <button class="profile-btn">
          <?php if (!empty($user['profile_picture'])): ?>
            <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="⚙️" class="profile-picture-thumbnail">
          <?php else: ?>
            <i class="fas fa-user-circle"></i>
          <?php endif; ?>
        </button>
        <div class="dropdown-content">
          <?php if (isLoggedIn()): ?>
            <!--<?= safe_url('/chemin/index.php') ?>--remplace--<?= BASE_URL ?>--?-->
            <a href="<?= BASE_URL ?>/pages/profile.php">
              <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="⚙️" class="profile-picture-small">
              <?php else: ?>
                <i class="fas fa-user-circle"></i>
              <?php endif; ?>
              <?= htmlspecialchars($user['username']) ?>
            </a>
            <a href="<?= BASE_URL ?>/pages/change-password.php"><i class="fas fa-key"></i> Changer mot de passe</a>
            <a href="<?= BASE_URL ?>/pages/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
          <?php endif; ?>
        </div>
      </div>

      <h1><?= SITE_NAME ?></h1>

      <div class="menu-dropdown">
        <button class="menu-btn">
          <i class="fas fa-bars"></i>
        </button>
        <div class="dropdown-content">
          <a href="<?= BASE_URL ?>"><i class="fa-solid fa-comments"></i> Comment</a>
          <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/pages/view_file.php"><i class="fa-solid fa-inbox"></i> Public file</a>
            <a href="<?= BASE_URL ?>/pages/gallery.php"><i class="fas fa-download"></i> Upload</a>
            <!--<a href="<?= BASE_URL ?>/pages/facture.php"><i class="fa-solid fa-receipt"></i> Create Invoice</a>
                        <a href="<?= BASE_URL ?>/pages/format.php"><i class="fas fa-file-csv"></i> Data to Table</a>-->
            <a href="<?= BASE_URL ?>/pages/data-to-chart.php"><i class="fa-solid fa-chart-line"></i> Data
              Visualizer</a>
            <!--<a href="<?= BASE_URL ?>/pages/codepen.php"><i class="fas fa-code"></i> Codepen</a>-->
          <?php endif; ?>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Gestion du clic pour les deux dropdowns
        document.querySelectorAll(".profile-btn, .menu-btn").forEach(btn => {
          btn.addEventListener("click", function (e) {
            e.stopPropagation(); // évite propagation du clic
            const parent = btn.closest(".profile-dropdown, .menu-dropdown");
            parent.classList.toggle("open");
          });
        });

        // Ferme le menu si clic en dehors
        document.addEventListener("click", function (e) {
          document.querySelectorAll(".profile-dropdown, .menu-dropdown").forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
              dropdown.classList.remove("open");
            }
          });
        });
      });
    </script>

  </header>

  <main>