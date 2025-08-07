<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // Validation
  if (empty($email)) {
    $errors[] = "L'email est requis";
  }

  if (empty($password)) {
    $errors[] = "Le mot de passe est requis";
  }

  // Si pas d'erreurs, vérifier les identifiants
  if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      header("Location: " . BASE_URL);
      exit;
    } else {
      $errors[] = "Email ou mot de passe incorrect";
    }
  }
}

require_once '../includes/header.php';
?>
<!-- presentation des tools de Data Visualizer 
<section class="data-tools-showcase">
    <div class="dt-container">
        <h2 class="dt-title">
            <span class="dt-icon"><i class="fas fa-chart-network"></i></span>
            Transformez vos données en insights
        </h2>

        <div class="dt-grid">
           
            <div class="dt-card">
                <div class="dt-card-icon csv">
                    <i class="fa fa-file-csv"></i>
                </div>
                <h3 data-i18n-card-title>CSV Transformer</h3>
                <p data-i18n-card-text>Conversion vers multiples formats</p>
                <ul class="dt-features">
                    <li><i class="fas fa-chart-bar"></i> Graphiques dynamiques</li>
                    <li><i class="fas fa-table"></i> Tableaux interactifs</li>
                    <li><i class="fas fa-file-export"></i> Exports PNG/PDF</li>
                </ul>
            </div>

            
            <div class="dt-card">
                <div class="dt-card-icon excel">
                    <i class="fa fa-file-excel"></i>
                </div>
                <h3 data-i18n-card-title>Excel Magic</h3>
                <p data-i18n-card-text>Analyse avancée</p>
                <ul class="dt-features">
                    <li><i class="fas fa-project-diagram"></i> Visualisations 3D</li>
                    <li><i class="fas fa-bolt"></i> Traitement rapide</li>
                    <li><i class="fas fa-cloud-upload"></i> Intégration cloud</li>
                </ul>
            </div>

            
            <div class="dt-card">
                <div class="dt-card-icon json">
                    <i class="fa fa-file-code"></i>
                </div>
                <h3 data-i18n-card-title>JSON Explorer</h3>
                <p data-i18n-card-text>Analyse de structures</p>
                <ul class="dt-features">
                    <li><i class="fas fa-sitemap"></i> Arborescence</li>
                    <li><i class="fas fa-filter"></i> Filtres intelligents</li>
                    <li><i class="fas fa-share-alt"></i> Partage configurable</li>
                </ul>
            </div>
        </div>

        <div class="dt-cta">
            <p data-i18n-card-text>Explorez notre galerie publique ou uploader vos propres fichiers</p>
            <div class="dt-buttons">
                
                <a href="#seconnecter" class="dt-btn primary">
                    <i class="fas fa-rocket"></i> Commencer
                </a>
                <a href="#" class="dt-btn secondary">
                    <i class="fas fa-book-open"></i> Tutoriels
                </a>
            </div>
        </div>
    </div>
</section>-->
<!-- FontAwesome CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
  @import url("https://fonts.googleapis.com/css?family=Montserrat:400,700");

  .agora-intro {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    font-family: "Montserrat", sans-serif;
    color: #222;
  }

  .agora-intro img {
    display: flex;
    margin: 0 auto;
    width: 400px;
    height: auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 16px;
    margin-bottom: 30px;
  }

  @media screen and (max-width: 400px) {
    .agora-intro img {
      width: 100%;
      max-width: 250px;
    }
  }

  .agora-intro h2 {
    text-align: center;
    margin-bottom: 30px;
  }

  .card-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
  }

  .card {
    flex: 1 1 250px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    padding: 25px 20px;
    text-align: center;
    transition: transform 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
  }

  .card i {
    font-size: 30px;
    margin-bottom: 15px;
  }

  .card h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #111;
  }

  .card p {
    font-size: 15px;
    color: #555;
    line-height: 1.5;
  }

  /* Icon Colors */
  .icon-red {
    color: #ee6055;
  }

  .icon-green {
    color: #60d394;
  }

  .icon-blue {
    color: #3498db;
  }

  .icon-yellow {
    color: #ffd97d;
  }

  .icon-purple {
    color: #ab9ff2;
  }

  .icon-orange {
    color: #f9b87fff;
  }

  /* Responsive */
  @media screen and (max-width: 400px) {
    .card {
      flex: 1 1 100%;
    }
  }
</style>

<section class="agora-intro">
  <img src="<?= BASE_URL ?>/assets/img/fullmotionpres.gif" alt="Agora Social Feed - Motion Design" />
  <h2 data-i18n="title">Bienvenue sur <strong>Agora</strong> Social Feed</h2>

  <div class="card-grid">

    <div class="card" data-i18n-card>
      <i class="fas fa-user-circle icon-blue"></i>
      <h3 data-i18n-card-title>Features</h3>
      <p data-i18n-card-text>Agora Social Feed contient un Réseau social minimaliste et une plateforme de partage et
        visualisation graphique de fichiers CSV, Excel et Json. </p>
    </div>

    <div class="card" data-i18n-card data-i18n-card>
      <i class="fas fa-user-secret icon-purple"></i>
      <h3 data-i18n-card-title data-i18n-card-title>Anonyme</h3>
      <p data-i18n-card-text>Inscription gratuite. Aucun mail vérifié requis. Crée un compte en quelques secondes, sans
        friction ni identité
        imposée.</p>
    </div>

    <!--<div class="card" data-i18n-card>
      <i class="fas fa-clock icon-blue"></i>
      <h3 data-i18n-card-title>Fil chronologique</h3>
      <p data-i18n-card-text>Les publications sont affichées dans l’ordre réel, sans algorithme ni tri caché. Ce que tu vois est ce qui est posté.</p>
    </div>

    <div class="card" data-i18n-card>
      <i class="fas fa-bolt icon-orange"></i>
      <h3 data-i18n-card-title>Sans scroll infini</h3>
      <p data-i18n-card-text>Tu parcours les posts à ton rythme, sans boucle addictive. Un usage sain et maîtrisé.</p>
    </div>-->

    <div class="card" data-i18n-card>
      <i class="fas fa-eye-slash icon-green"></i>
      <h3 data-i18n-card-title>Pas de tracking, pas de pub</h3>
      <p data-i18n-card-text>Aucune collecte ou exploitation des données. Pas de pub ni de notif.</p>
    </div>

    <!--<div class="card" data-i18n-card>
      <i class="fas fa-heart icon-yellow"></i>
      <h3 data-i18n-card-title>Respect obligatoire</h3>
      <p data-i18n-card-text>Agora est un espace bienveillant. Tout comportement malveillant est banni sans préavis.</p>
    </div>-->

    <div class="card" data-i18n-card>
      <i class="fas fa-flask icon-yellow"></i>
      <h3 data-i18n-card-title>En test, avec toi</h3>
      <p data-i18n-card-text>Agora est un prototype. Tes retours sont les bienvenus pour co-construire cet espace suivant les besoins de chacun.</p>
    </div>

    <!--<div class="card" data-i18n-card>
      <i class="fas fa-lightbulb icon-green"></i>
      <h3 data-i18n-card-title>Pourquoi Agora ?</h3>
      <p data-i18n-card-text>Parce que tu mérites un lieu d’expression sans filtre ni influence. Sobre. Humaine. Authentique.</p>
    </div>-->

  </div>
</section>



<div class="container auth-container" id="seconnecter">
  <h2>Connexion</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $error): ?>
        <p data-i18n-card-text><?= $error ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success">
      Inscription réussie ! Vous pouvez maintenant vous connecter.
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>
    </div>

    <div class="form-group">
      <label for="password">Mot de passe:</label>
      <input type="password" id="password" name="password" required>
    </div>

    <button type="submit">Se connecter</button>
  </form>

  <p data-i18n-card-text>Pas encore de compte ? <a href="register.php">Inscrivez-vous</a></p>
  <p data-i18n-card-text><a href="forgot-password.php">Mot de passe oublié ?</a></p>
</div>

<script src="/assets/js/lang.js"></script>

<?php require_once '../includes/footer.php'; ?>