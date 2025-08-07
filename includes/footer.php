<footer class="data-footer">
  <div class="footer-grid">
    <!--     
  FileShare
    Visualise et partage tes données — sans compte, sans email, sans pub.

    🔍 Lis les fichiers CSV, Excel, JSON

    📊 Transforme-les en graphiques propres

    📁 Exporte en PDF ou PNG

    🕶️ Utilisation anonyme, sans traçage
     -->
    <div class="footer-brand">
      <h3 class="app-name"><?= SITE_NAME ?> Social Feed</h3>
      <p class="tagline">MVP (prototype) Contient un Réseau social minimaliste et une plateforme de partage et visualisation graphique de fichiers CSV, Excel et Json. Réseaux communautaire low key, no email verification required.</p>
      <div class="stats">
        <div class="stat-item">
          <i class="fas fa-database"></i>
          <span><?php echo number_format($pdo->query("SELECT COUNT(id) FROM user_files")->fetchColumn()); ?>
            Data Files</span>
        </div>
        <div class="stat-item">
          <i class="fas fa-users"></i>
          <span><?php echo number_format($pdo->query("SELECT COUNT(id) FROM users")->fetchColumn()); ?>
            Data Enthusiasts</span>
        </div>

      </div>
    </div>

    <!-- Section Fonctionnalités -->
    <div class="footer-features">
      <h4>Fonctionnalités</h4>
      <ul>
        <li><i class="fa-solid fa-comments"></i><a href="../pages/home.php">Social Feed - Post/comment/like</a></li>
        <li><i class="fas fa-file-pdf"></i><a href="../pages/gallery.php"> Excel & Csv to Chart/PDF</a></li><!--attention tout les json ne se mette pas en tableau et le telechargement est uniquement en json, non en pdf pour l'instant-->
        <li><i class="fas fa-chart-line"></i><a href="../pages/data-to-chart.php"> Data Visualizer</a></li>
        <li><i class="fas fa-file-export"></i><a href="../pages/search.php"> Public Data</a></li>
        <!--<li><i class="fas fa-download"></i> Imports</li>
        <li><i class="fas fa-file-export"></i> Exports</li>
        <li><i class="fa-solid fa-receipt"></i><a href="../pages/facture.php"> Create Invoice</a></li>
        <li><i class="fas fa-sync-alt"></i> Mises à jour automatiques des prix</li>-->
        <li><i class="fas fa-code"></i><a href="../pages/codepen.php"> Live code editor</a></li>
        <a href="#" class="social-icon"><i class="fa-brands fa-google-play"></i> Télécharger l'app</a>
      </ul>
    </div>

    <div class="footer-contact">
      <h4>More +</h4>
      <div class="social-links">
        <a href="https://github.com/berru-g/" class="social-icon"><i class="fab fa-github"></i></a>
        <a href="https://codepen.io/h-lautre" class="social-icon"><i class="fab fa-codepen"></i></a>
        <a href="https://gael-berru.netlify.app/#contact" class="social-icon" target="_blank"><i
            class="fa-solid fa-headset"></i></a>
        <a href="#" class="social-icon"><i class="fa-brands fa-medium"></i></a>
        <a href="https://gael-berru.netlify.app/donation" class="social-icon"><i class="fa-solid fa-mug-hot"></i></a>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y') ?> <?= SITE_NAME ?> by <a href="https://gael-berru.com">berru-g</a> - Tous droits réservés</p>
    <div class="legal-links">
      <a href="#">CGU</a> | <a href="#">Confidentialité</a>
    </div>
  </div>

</footer>

<style>
  :root {
    --background-color: #f1f1f1;
    --text-color: #000000;
    --titre-color: #6c757d;
    --primary-color: #ab9ff2;
    --secondary-color: #ffffff;
    --border-color: #e0e0e0;
    --shadow-color: rgba(0, 0, 0, 0.08);
    --input-background: #f9f9f9;
    --accent-color: #2575fc;
    --success-color: #60d394;
    --error-color: #ee6055;
    --jaune-color: #ffd97d;
    --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }

  .data-footer {
    background: #222;
    color: #ecf0f1;
    padding: 40px 0 0;
    font-family: 'Segoe UI', Roboto, sans-serif;
    box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.1);
  }

  .footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
  }

  .footer-features a {
    text-decoration: none;
    color: whitesmoke;
  }
  .footer-features a:hover {
    text-decoration: underline;
  }

  .app-name {
    color: #ab9ff2;
    font-size: 1.5rem;
    margin-bottom: 10px;
    font-weight: 700;
  }

  .tagline {
    color: #bdc3c7;
    font-size: 0.9rem;
    margin-bottom: 20px;
  }

  .stats {
    display: flex;
    gap: 15px;
  }

  .stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
    color: #95a5a6;
  }

  .stat-item i {
    color: #ab9ff2;
  }

  h4 {
    color: #ab9ff2;
    font-size: 1.1rem;
    margin-bottom: 15px;
    position: relative;
    padding-bottom: 5px;
  }

  h4::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 2px;
    background: #ab9ff2;
  }

  ul {
    list-style: none;
    padding: 0;
  }

  ul li {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
  }

  ul li i {
    color: #ab9ff2;
    width: 20px;
    text-align: center;
  }

  .dev-contact {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #ecf0f1;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 4px;
    transition: all 0.3s;
    background: rgba(255, 255, 255, 0.05);
  }

  .dev-contact:hover {
    background: rgba(52, 152, 219, 0.2);
    transform: translateY(-2px);
  }

  .social-links {
    display: flex;
    gap: 10px;
    margin-top: 15px;
  }

  .social-icon {
    color: #bdc3c7;
    font-size: 1.1rem;
    transition: all 0.3s;
  }

  .social-icon:hover {
    color: #ab9ff2;
    transform: scale(1.2);
  }

  .footer-links {
    display: flex;
    align-items: center;
  }

  .personal-link {
    color: grey;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 8px 15px;
    border-radius: 4px;
    transition: all 0.3s ease;
    background-color: rgba(255, 255, 255, 0.1);
  }

  .footer-bottom {
    text-align: center;
    padding: 20px;
    margin-top: 40px;
    background: rgba(0, 0, 0, 0.2);
    font-size: 0.8rem;
    color: #95a5a6;
  }
  .footer-bottom a {
    font-size: 0.8rem;
    color: #ab9ff2 ;
    text-decoration: none;
  }

  .legal-links {
    margin-top: 10px;
  }

  .legal-links a {
    color: #bdc3c7;
    text-decoration: none;
    transition: color 0.3s;
  }

  .legal-links a:hover {
    color: #ab9ff2;
  }

  @media (max-width: 768px) {
    .footer-grid {
      grid-template-columns: 1fr;
    }

    .stats {
      justify-content: center;
    }
  }
</style>