✅ CHECKLIST DÉMARCHE - MISE EN LIGNE D’UN SITE PHP + MySQL SUR HOSTINGER
(Avec nom de domaine et base de données)
🔹 1. Création de compte & achat nom de domaine

Créer un compte sur https://www.hostinger.fr

Choisir un hébergement Web Premium ou supérieur (avec base MySQL).

Acheter ou choisir un nom de domaine (ex : monsite.com).

    Lier le domaine à l’hébergement (si acheté ailleurs, configurer les DNS).

🔹 2. Configuration de l’hébergement

Accéder au tableau de bord Hostinger → section Sites Web → Gérer.

    Aller dans Base de données > MySQL :

        Créer une nouvelle base de données

        Noter les infos suivantes :

            Nom de la base

            Nom d’utilisateur

            Mot de passe

            Hôte (Host) → souvent localhost

🔹 3. Préparer la base de données
⚙ En local :

    Ouvrir phpMyAdmin (MAMP) → Exporter ta base en .sql

🌐 En ligne :

Aller dans Base de données > phpMyAdmin

Importer le fichier .sql précédemment exporté

    Vérifier si toutes les tables sont bien présentes

🔹 4. Préparer les fichiers du site

Vérifier que tous les chemins sont relatifs (/assets/, /includes/, etc.)

    Modifier ton fichier config.php :

define('DB_HOST', 'localhost');
define('DB_NAME', 'nom_base_hostinger');
define('DB_USER', 'utilisateur_hostinger');
define('DB_PASS', 'mot_de_passe_hostinger');
define('BASE_URL', 'https://www.tondomaine.com');

    Supprimer les fichiers de dev inutiles (.DS_Store, .env, fichiers tests...)

🔹 5. Uploader ton site sur Hostinger

Aller dans Files > Gestionnaire de fichiers ou utiliser FileZilla

    Connexion FTP : dispo dans “Comptes FTP”

Uploader tout le site dans le dossier public_html/

    Vérifier la structure : le fichier index.php doit être à la racine du dossier

🔹 6. Sécurité & paramétrages serveur

Activer le SSL (HTTPS) :

    Dans Hostinger > SSL > Activer Let's Encrypt

    Forcer le HTTPS (dans .htaccess) :

RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    Vérifier les permissions des dossiers :

        uploads/ ou images/ → 755 ou 775

        Pas de droits en écriture globale (777 à éviter)

🔹 7. Tester ton site

Naviguer sur toutes les pages

    Tester :

        Inscription / connexion

        Formulaires (contact, upload, etc.)

        Uploads / Téléchargements

        Accès aux données utilisateurs (dashboard, profil)

        Admin interface (si présente)

🔹 8. Checklist bonus (optionnel mais utile)

Ajouter favicon et balises SEO (<meta>)

Intégrer Google Analytics ou Matomo

Faire une sauvegarde automatique de la base (cron possible)

Ajouter un fichier robots.txt et un sitemap.xml

Tester sur mobile (responsive)

    Configurer les emails SMTP si ton site envoie des mails

📦 Dossier final conseillé

/public_html
  ├── index.php
  ├── /includes
  ├── /pages
  ├── /assets
  ├── /uploads
  ├── config.php
  └── .htaccess

🔚 Résultat attendu :

Ton site PHP avec base MySQL fonctionne parfaitement, sécurisé avec HTTPS, en ligne sous ton propre domaine.


# Config local/hebergemlent

Checklist "Quick Setup" pour plus tard :

    Crée 2 fichiers à la racine :

        .env.local (pour ta config locale)

        .env.prod (pour ton hébergeur)

    Dans ton config.php :
    php

$isLocal = ($_SERVER['SERVER_ADDR'] === '127.0.0.1'); // Auto-détection
require_once($isLocal ? '.env.local' : '.env.prod');

GitIgnore :
Ajoute cette ligne dans ton .gitignore :
gitignore

.env.local
.env.prod

Bonus Flemme Mode :
Si t’as la flemme de gérer les variables d’env, fais juste ça :
php

// config.php
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // Tes params locaux
} else {
    // Tes params prod
}