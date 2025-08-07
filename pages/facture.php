<?php
require_once  '../includes/config.php';
require_once  '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$user = getUserById($_SESSION['user_id']);

require_once  '../includes/header.php';
?>

<div class="container profile-container">
    <h2><?= htmlspecialchars($user['username']) ?></h2>
    
    <div class="profile-info">
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    </div>
    
    <style>
        :root {
    --primary: #ab9ff2;
    --text-dark: #333;
    --text-light: #fff;
    --background: #f4f3fc;
    --box-background: #ffffff;
    --border-color: #ddd
}


.download-btn,
.footer,
h2 {
    margin-top: 2rem
}

.invoice-box {
    width: 100%;
}

.download-btn,
table th {
    background: var(--primary)
}

h1 {
    margin-bottom: .5rem
}

h2 {
    font-size: 1.2rem;
    border-bottom: 2px solid var(--primary);
    padding-bottom: .3rem
}

input[type=text] {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid var(--border-color);
    border-radius: 6px
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: .95rem
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem
}

table td,
table th {
    border: 1px solid var(--border-color);
    padding: 10px;
    text-align: left
}

table th {
    color: var(--text-light)
}

.right {
    text-align: right
}

.footer {
    font-size: .85rem;
    color: #777
}

.download-btn {
    display: inline-block;
    color: var(--text-light);
    padding: .6rem 1.2rem;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 700;
    transition: background .2s
}

.download-btn:hover {
    background: #9789db
}
</style>

    <form id="devisForm">
    <div class="invoice-box" id="invoice">
      <p><strong>Devis N°:</strong> <span id="invoice-number"></span><br>
        <strong>Date:</strong> <span id="invoice-date"></span>
      </p>

      <h2>Client</h2>
      <p>
        <input type="text" id="client-name" placeholder="Nom du client">
        <input type="text" id="client-email" placeholder="Email du client">
      </p>

      <h2>Les bases :</h2>
      <div id="service-form">
        <label><input type="checkbox" class="item" name="developpement_vitrine" data-label="Développement site vitrine"
            data-price="550"> Développement
          site vitrine (550 €)</label>
        <label><input type="checkbox" class="item" name="formulaire_simple" data-label="Formulaire simple"
            data-price="50"> Formulaire de contact
          simple (50 €)</label>
        <label><input type="checkbox" class="item" name="formulaire_complexe" data-label="Formulaire et bdd"
            data-price="400"> Formulaire de contact
          complexe (400 €)</label>
        <label><input type="checkbox" class="item" name="optimisation_seo" data-label="Optimisation SEO"
            data-price="100"> Optimisation SEO (100
          €)</label>
        <h2>Passer à la vente en ligne :</h2>
        <label><input type="checkbox" class="item" name="systeme_paiement" data-label="Système de paiement"
            data-price="500"> Système de paiement
          (500 €)</label>
        <label><input type="checkbox" class="item" name="interface_admin" data-label="Interface admin" data-price="100">
          Interface admin (100
          €/an)</label>
        <h2>Pour une offre "je veux un site clef en main" :</h2>
        <label><input type="checkbox" class="item" name="nom_domaine" data-label="nom de domaine" data-price="10"> Nom
          de domaine (10
          €/an)</label>
        <label><input type="checkbox" class="item" name="hebergement" data-label="hébergement" data-price="80">
          hébergement (80 €/an)</label>
      </div>

      <table id="invoice-table">
        <thead>
          <tr>
            <th>Description</th>
            <th>Qté</th>
            <th>Prix unitaire</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody id="invoice-body"></tbody>
      </table>

      <p class="right"><strong>Total à payer :</strong> <span id="invoice-total">0 €</span></p>

      <div class="footer">
        <p>TVA non applicable, article 293 B du CGI.</p>
        <p>SIRET : 123 456 789 00000</p>
      </div>
    </div>

    <div class="action-buttons">
      
      <button type="button" class="download-btn" onclick="downloadPDF()">Télécharger le devis (PDF)</button>
      <button type="submit" class="download-btn" style="text-decoration:line-through">Envoyer le devis</button>
    </div>
  </form>



  </div>


  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="../assets/js/facture.js"></script>


<?php require_once  '../includes/footer.php'; ?>