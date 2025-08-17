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
    content="agora data viz est une plateforme de partage de fichiers, convertisseur de format,  avec visualisation et export de données (CSV, JSON, Excel, PDF, PNG) et un réseau social.">
  <meta name="keywords"
    content="convert csv json, json editor, sql editor, csv to pdf, json to mindmap, json to csv, csv to json, csv to pdf, csv to png, xlxs to chart, JSON Mind Mapper, json to map, data visualizer, excel to chart,  Data Enthusiasts, plateforme collaboratif, outil data, réseau social">
  <meta name="robots" content="index, follow">
  <meta name="author" content="agora data viz">

  <!-- Open Graph pour Facebook / LinkedIn -->
  <meta property="og:title" content="agora data viz – Partage intelligent de fichiers et données">
  <meta property="og:description"
    content="Réseau social minimaliste. Partagez, visualisez et exportez vos fichiers CSV, Excel, JSON et PDF. Outil collaboratif tout-en-un.">
  <meta property="og:image" content="<?= BASE_URL ?>/assets/img/agora-logo.png">
  <meta property="og:url" content="https://agora-dataviz.com">
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="agora dataviz – Partage et visualisation de fichiers JSON/CSV/Excel">
  <meta name="twitter:description"
    content="Transformez vos données avec agora data viz. Partagez, visualisez et exportez tous types de fichiers.">
  <meta name="twitter:image" content="<?= BASE_URL ?>/assets/img/agora-logo.png">
  <!-- Canonical URL -->
  <link rel="canonical" href="https://agora-dataviz.com">

  <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> revoir tout le css bootstrap c'est vrt de la grosse merde-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
  <!-- CSS AOS -->
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // Fail-safe si jQuery ne charge pas
    window.jQuery || document.write('<script src="<?= BASE_URL ?>/assets/js/jquery.min.js">\x3C/script>');
  </script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/fontawesome.min.css" rel="stylesheet">
  <!--pwa-->
  <link rel="manifest" href="/manifest.json">
  <!-- Meta pour iOS (optionnel mais utile) -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="Mon App">
  <link rel="apple-touch-icon" href="/icon-192x192.png">
  <!-- Couleur de thème -->
  <meta name="theme-color" content="#ab9ff2" />
  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "agora data viz",
  "url": "https://agora-dataviz.com",
  "description": "Plateforme de partage et de visualisation de fichiers CSV, JSON, Excel, avec export PDF/PNG et réseau social intégré.",
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
  "name": "Comment utiliser agora data viz pour transformer un fichier CSV en graphique et PDF ?",
  "step": [
    {
      "@type": "HowToStep",
      "text": "Connectez-vous gratuitement à agora data viz."
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
      "name": "Data Visualizer"
    }
  ],
  "totalTime": "PT1M",
  "image": "https://agora-dataviz.com/assets/img/agora-logo.png"
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
          <a href="<?= BASE_URL ?>"><i class="fa-solid fa-comments"></i> Feed</a>
          <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/pages/view_file.php"><i class="fa-solid fa-inbox"></i> Public file</a>
            <a href="<?= BASE_URL ?>/pages/gallery.php"><i class="fas fa-download"></i> Upload</a>
            <a href="<?= BASE_URL ?>/pages/data-to-chart.php"><i class="fa-solid fa-chart-line"></i> Data
              Visualizer</a>
            <a href="<?= BASE_URL ?>/pages/json-to-map.php"><i class="fas fa-project-diagram"></i> Json to Map</a>
            <a href="<?= BASE_URL ?>/pages/SQL-editor.php"><i class="fas fa-database"></i> SQL Editor</a>
            
            <a href="#" class="pwa-btn" id="installBtn"><i class="fa-brands fa-google-play"></i> L'app</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <script>
      // Service Worker pour PWA
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/sw.js')
            .then(registration => console.log('SW enregistré avec succès !'))
            .catch(err => console.error('Échec de l\'enregistrement du SW :', err));
        });
      }

      // Gestion de l'installation PWA
      let deferredPrompt;
      const installBtn = document.getElementById('installBtn');

      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        // Affiche le bouton uniquement si l'app n'est pas déjà installée
        if (!isPWAInstalled()) {
          installBtn.style.display = 'block';
        }
      });

      // Gestion du clic sur le bouton d'installation
      if (installBtn) {
        installBtn.addEventListener('click', (e) => {
          e.preventDefault();

          if (deferredPrompt) {
            deferredPrompt.prompt();

            deferredPrompt.userChoice.then((choiceResult) => {
              if (choiceResult.outcome === 'accepted') {
                console.log('L\'utilisateur a accepté l\'installation');
                installBtn.style.display = 'none';
              }
              deferredPrompt = null;
            });
          } else {
            console.log('Le prompt d\'installation n\'est pas disponible');
            // Fallback pour les navigateurs qui ne supportent pas l'API
            showManualInstallInstructions();
          }
        });
      }

      // Vérifie si l'app est déjà installée
      function isPWAInstalled() {
        return window.matchMedia('(display-mode: standalone)').matches ||
          navigator.standalone ||
          document.referrer.includes('android-app://');
      }

      // Cache le bouton si l'app est déjà installée
      window.addEventListener('appinstalled', () => {
        console.log('PWA déjà installée');
        if (installBtn) installBtn.style.display = 'none';
      });

      // Vérification au chargement
      document.addEventListener('DOMContentLoaded', () => {
        if (isPWAInstalled() && installBtn) {
          installBtn.style.display = 'none';
        }
      });

      // Fallback pour les navigateurs moins supportés
      function showManualInstallInstructions() {
        // Tu peux ajouter une modal ou un tooltip ici
        console.log('Instructions manuelles pour installer la PWA');
        alert("Pour installer l'application :\n\n- Sur Chrome/Edge : cliquez sur l'icône 'Installer' dans la barre d'adresse\n- Sur iOS : utilisez l'option 'Partager' puis 'Ajouter à l'écran d'accueil'");
      }

      // Gestion des menus déroulants (existant - gardé pour référence)
      document.querySelectorAll(".profile-btn, .menu-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
          e.stopPropagation();
          const parent = btn.closest(".profile-dropdown, .menu-dropdown");
          parent.classList.toggle("open");
        });
      });
      // ferme le menu si click externe
      document.addEventListener("click", function (e) {
        document.querySelectorAll(".profile-dropdown, .menu-dropdown").forEach(dropdown => {
          if (!dropdown.contains(e.target)) {
            dropdown.classList.remove("open");
          }
        });
      });
    </script>


    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-GDYE1C3T45"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag() { dataLayer.push(arguments); }
      gtag('js', new Date());

      gtag('config', 'G-GDYE1C3T45');
    </script>


  </header>

  <main>