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

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet" />

<style>
  @import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap");

  /* Reset & base */
  * {
    box-sizing: border-box;
  }

  body {
    margin: 0;
    background: #f5f6fa;
    font-family: "Montserrat", sans-serif;
    color: #2c2c3a;
    overflow-x: hidden;
  }

  a {
    color: #9c8dea;
    text-decoration: none;
  }

  a:hover {
    text-decoration: underline;
  }

  /* Canvas background container */
  #canvas-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    background: linear-gradient(135deg, #f0f0fc, #d8d6f9);
  }

  /* Container */
  .agora-intro {
    max-width: 900px;
    margin: 70px auto 100px;
    padding: 0 20px;
  }

  /* Hero Section */
  .hero {
    text-align: center;
    margin-bottom: 60px;
  }

  .hero img {
    width: 280px;
    max-width: 90vw;
    border-radius: 20px;
    box-shadow: 0 12px 30px rgba(156, 141, 234, 0.45);
    margin-bottom: 30px;
    filter: drop-shadow(0 0 12px #9c8dea88);
    transition: filter 0.3s ease;
  }

  .hero img:hover {
    filter: drop-shadow(0 0 20px #9c8deacc);
  }

  .hero h1 {
    font-weight: 700;
    font-size: 3rem;
    color: #4b47a1;
    margin-bottom: 8px;
    text-shadow: 0 0 8px #9c8deaaa;
  }

  .hero h1 strong {
    color: #9c8dea;
  }

  .hero p {
    font-size: 1.2rem;
    max-width: 580px;
    margin: 0 auto 30px;
    color: #5a5780;
  }

  .hero .btn-primary {
    background: #9c8dea;
    border: none;
    padding: 15px 40px;
    color: white;
    font-weight: 600;
    border-radius: 30px;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(156, 141, 234, 0.6);
    transition: all 0.3s ease;
    font-size: 1.1rem;
  }

  .hero .btn-primary:hover {
    background: #7f6ed7;
    box-shadow: 0 9px 25px rgba(127, 110, 215, 0.8);
    transform: translateY(-3px);
  }

  /* Card Grid */
  .card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 30px;
    margin-top: 40px;
  }

  /* Card */
  .card {
    background: white;
    border-radius: 18px;
    border: 1px solid white;
    padding: 30px 25px 35px;
    box-shadow:
      0 2px 8px rgba(156, 141, 234, 0.12),
      0 12px 20px rgba(156, 141, 234, 0.18);
    text-align: center;
    cursor: default;
    transition:
      box-shadow 0.4s ease,
      transform 0.3s ease,
      background 0.3s ease;
    will-change: transform;
    position: relative;
    overflow: hidden;
  }

  .card::before {
    content: "";
    position: absolute;
    top: -40%;
    left: -40%;
    width: 180%;
    height: 180%;
    background: linear-gradient(120deg,
        #9c8dea,
        #7869e2,
        #b5a9f8,
        #9c8dea);
    filter: blur(55px);
    opacity: 0.3;
    transition: opacity 0.5s ease;
    z-index: 0;
    border-radius: 50%;
  }

  .card:hover::before {
    opacity: 0.55;
  }

  .card:hover {
    transform: translateY(-12px);
    box-shadow:
      0 6px 24px rgba(156, 141, 234, 0.35),
      0 20px 36px rgba(156, 141, 234, 0.3);
    background: #f9f8ff;
  }

  .card i {
    font-size: 40px;
    color: #fff;
    margin-bottom: 18px;
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
  }

  .card:hover i {
    transform: scale(1.25) rotate(10deg);
  }

  .card h3 {
    font-size: 22px;
    margin-bottom: 15px;
    color: #5a5780;
    position: relative;
    z-index: 1;
  }

  .card p {
    font-size: 15.8px;
    line-height: 1.5;
    color: #777493;
    position: relative;
    z-index: 1;
  }

  /* Responsive */
  @media (max-width: 480px) {
    .hero h1 {
      font-size: 2.2rem;
    }

    .hero p {
      font-size: 1rem;
    }
  }
</style>

<section class="agora-intro">
  <canvas id="canvas-bg"></canvas>

  <div class="hero" data-aos="fade-up" data-aos-duration="1200">
<!--Approches séduisantes pour parler des usages
Par scénario concret
"Vous venez de recevoir un fichier Excel plein de chiffres ? En quelques clics, Agora Dataviz le transforme en un graphique clair et dynamique, parfait pour votre réunion de 15h."
"Vous voulez publier une étude sur LinkedIn ? Glissez vos données dans Agora Dataviz, choisissez un style, téléchargez l’image prête à poster."
Par gain immédiat
"Passez de la donnée brute à un graphique interactif en moins de 2 minutes."
"Fini les tableurs indigestes — vos chiffres racontent enfin une histoire."
Par émotion et image mentale
"Faites parler vos données comme si vous étiez un designer."
"Des chiffres qui séduisent l’œil, des graphiques qui captivent l’audience."-->
    <h1>
       habillez vos données pour convaincre
    </h1>
    <p>
      en moins de 2 minutes.
    </p>
    <a href="<?= BASE_URL ?>/pages/register.php"><button class="btn-primary"
        aria-label="Essayer Agora Social Feed maintenant">
        Créer gratuitement
      </button></a>
  </div>

  <div class="card-grid" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1000">
    <div class="card" data-i18n-card>
      <i class="fas fa-chart-line"></i>
      <h3 data-i18n-card-title>Features</h3>
      <p data-i18n-card-text>
        Poster vos dernières trouvailles, partagez vos data public, créer une map depuis un json ou une chart depuis un
        csv, éditez du json ou SQL. Convertissez vos formats et bien plus.
      </p>
    </div>

    <div class="card" data-i18n-card>
      <i class="fas fa-user-secret"></i>
      <h3 data-i18n-card-title>Anonyme</h3>
      <p data-i18n-card-text>
        Inscription gratuite. Aucun mail vérifié requis, sans friction ni identité imposée. Base de données sécurisé.
      </p>
    </div>

    <div class="card" data-i18n-card>
      <i class="fas fa-eye-slash"></i>
      <h3 data-i18n-card-title>Pas de tracking, pas de pub</h3>
      <p data-i18n-card-text>
        Aucune collecte ou exploitation des données. Pas de pub ni de notif.
      </p>
    </div>

    <div class="card" data-i18n-card>
      <i class="fas fa-flask"></i>
      <h3 data-i18n-card-title>En test avec toi</h3>
      <p data-i18n-card-text>
        Agora est un MVP ou prototype. Vos retours sont les bienvenus pour co-construire cet
        espace suivant vos besoins.
      </p>
    </div>
  </div>
</section>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init({
    once: true,
    easing: "ease-in-out-cubic",
    duration: 900,
  });

  // Canvas background - points network (simple, performant)

  const canvas = document.getElementById("canvas-bg");
  const ctx = canvas.getContext("2d");
  let width, height;
  let points = [];

  function resize() {
    width = window.innerWidth;
    height = window.innerHeight;
    canvas.width = width * devicePixelRatio;
    canvas.height = height * devicePixelRatio;
    canvas.style.width = width + "px";
    canvas.style.height = height + "px";
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(devicePixelRatio, devicePixelRatio);
  }

  class Point {
    constructor(x, y, vx, vy) {
      this.x = x;
      this.y = y;
      this.vx = vx;
      this.vy = vy;
      this.radius = 2;
    }
    update() {
      this.x += this.vx;
      this.y += this.vy;
      if (this.x < 0 || this.x > width) this.vx = -this.vx;
      if (this.y < 0 || this.y > height) this.vy = -this.vy;
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
      ctx.fillStyle = "rgba(156, 141, 234, 0.7)";
      ctx.fill();
    }
  }

  function connectPoints() {
    let maxDist = 130;
    for (let i = 0; i < points.length; i++) {
      for (let j = i + 1; j < points.length; j++) {
        let dx = points[i].x - points[j].x;
        let dy = points[i].y - points[j].y;
        let dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < maxDist) {
          ctx.beginPath();
          ctx.strokeStyle = `rgba(156, 141, 234, ${1 - dist / maxDist})`;
          ctx.lineWidth = 1;
          ctx.moveTo(points[i].x, points[i].y);
          ctx.lineTo(points[j].x, points[j].y);
          ctx.stroke();
        }
      }
    }
  }

  function animate() {
    ctx.clearRect(0, 0, width, height);
    points.forEach((p) => {
      p.update();
      p.draw();
    });
    connectPoints();
    requestAnimationFrame(animate);
  }

  function init() {
    points = [];
    for (let i = 0; i < 40; i++) {
      let x = Math.random() * width;
      let y = Math.random() * height;
      let vx = (Math.random() - 0.5) * 0.3;
      let vy = (Math.random() - 0.5) * 0.3;
      points.push(new Point(x, y, vx, vy));
    }
    animate();
  }

  window.addEventListener("resize", () => {
    resize();
    init();
  });

  resize();
  init();
</script>




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
  <!--<p data-i18n-card-text><a href="forgot-password.php">Mot de passe oublié ?</a></p>-->
</div>

<script src="/assets/js/lang.js"></script>

<?php require_once '../includes/footer.php'; ?>