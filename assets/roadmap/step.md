📦 Dossier final conseillé

/public_html
  ├── index.php
  ├── /includes
  ├── /pages
  ├── /assets
  ├── /uploads
  ├── config.php
  └── .htaccess


---

## ✅ Checklist de protection avant mise en production

### 🔐 Sécurité des fichiers & accès
- ✅ Droits d’accès aux fichiers (**CHMOD 644** pour fichiers, **755** pour dossiers)
- ✅ `.gitignore` (logs, `.env`, fichiers de config sensibles)
- ✅ Désactiver l’indexation des dossiers (`Options -Indexes` dans `.htaccess`)
- 🔜 Supprimer tous les fichiers inutiles (ex: `test.php`, `info.php`, `backup.sql`, etc.)

---

### 🧱 Sécurité des données & code
- ✅ Protection contre l’injection SQL (requêtes préparées, ORM, etc.)
- ✅ Protection XSS (échappement des variables côté front & back)
- ✅ Protection contre injection JS (sanitize HTML / désactiver `innerHTML` non sûr)
- 🔜 Limiter la taille des entrées utilisateur (**POST** / **GET** / **input**)

---

### 🛡️ Sécurité des formulaires
- ✅ Système de modération (mots-clés à bannir, regex)
- 🔜 Honeypot anti-bot
- 🔜 Google reCAPTCHA (v2 ou v3)
- 🔜 Limitation de fréquence (ex: max 3 formulaires/minute par IP)

---

### 👮‍♂️ Auth & Brut Force
- ✅ Protection contre attaques brut force (limiter tentatives login)
- ✅ Temps d’attente progressif après échecs (ex: +5s par tentative)
- 🔜 Déconnexion auto après X minutes d’inactivité
- 🔜 Logs d’activité utilisateur suspecte

---

### 🧰 Outils & surveillance
- 🔜 Système de log d’erreurs personnalisées (avec IP, URI, timestamp)
- 🔜 Détection d’anomalies (ex : activité étrange sur un compte)
- 🔜 Alertes email sur erreurs critiques ou spam détecté
- 🔜 Intégration avec Cloudflare ou autre WAF (pare-feu applicatif)

---

### 🔒 HTTPS & headers
- 🔜 Redirection HTTPS forcée
- 🔜 Headers de sécurité :
  - `Content-Security-Policy`
  - `X-Frame-Options`
  - `Strict-Transport-Security`
  - `X-XSS-Protection`
  - `X-Content-Type-Options: nosniff`

---

### 🎁 Bonus (si site public)
- ✅ Page **404 personnalisée**
- 🔜 Page maintenance en cas de mise à jour
- ✅ Affichage limité d’erreurs PHP (pas en prod !)

---

## 1️⃣ Checklist Technique – Prêt pour le lancement

### 🌍 Nom de domaine & hébergement
- 🔜 Acheter ton nom de domaine (ex. AgoraSocial.com ou variante)
- 🔜 Configurer DNS → A record pour ton serveur *FAIT AUTO PAR HOSTINGER*
- 🔜 Installer SSL (via Let’s Encrypt ou Certbot) **Verif  où pointe ton vscode "git remote -v" puis ajouter ta clef ssh, generer "ssh-keygen -t ed25519"puis ajouter ()**
 - ✅ Re-deploy auto apres nomdedomaine, change public_html & redeploy.
- 🔜 Forcer HTTPS sur tout le site *FAIT AUTO PAR HOSTINGER*

#### Gérer les permissions aux fichiers :
 - .htaccess → 644
 - Fichiers de config → 600
 - Dossiers → 755

---

### 📈 Structure & SEO de base
- 🔜 Configurer URL propres (`/profil/username`) avec `.htaccess` ou router PHP
- 🔜 Ajouter `<title>` unique par page (pas "Accueil" partout)
- 🔜 Mettre `<meta name="description">` optimisé pour chaque page
- ✅ Ajouter un **favicon** et Open Graph (prévisualisation pour réseaux sociaux)
- 🔜 Créer un `sitemap.xml` et le soumettre à Google Search Console
- 🔜 Créer un `robots.txt` autorisant l’indexation des pages publiques
- 🔜 Minifier CSS/JS pour accélérer le site
- 🔜 Tester vitesse avec [PageSpeed Insights](https://pagespeed.web.dev/)

---

### 🔐 Sécurité
- ✅ Vérifier que les formulaires sont protégés contre XSS/CSRF
- ✅ Limiter tentatives de login (protection brute force)
- 🔜 Sauvegarde automatique base de données + fichiers (cron job)

---

### 📊 Tracking & analyse
- ✅ Installer Google Analytics 4 ou Plausible (plus RGPD friendly)
- 🔜 Configurer Google Search Console pour suivre indexation
- 🔜 (Optionnel) Heatmap type Hotjar pour voir le comportement des visiteurs

---

## 2️⃣ Checklist Communication – Créer le buzz

### 🎨 Identité & visuel
- ✅ Logo + déclinaisons (fond clair / fond sombre)
- Kit graphique : couleurs, polices, icônes (cohérence partout)
  - ✅ Motion design d’intro Agora (via Jitter) → teaser vidéo

---

### ⏳ Pré-lancement (teasing)
- 1 post teasing par jour sur 7 jours avant ouverture
- Captures d’écran floutées + phrases intrigantes ("Et si on parlait enfin sans algorithme ?")
  - Compte à rebours en story Instagram/Twitter

---

### 🚀 Lancement
- Publication simultanée sur Twitter, LinkedIn, Instagram, TikTok, Mastodon
- Ajouter hashtags ciblés (#AgoraSocial #NoAlgorithm #RéseauChrono)
- Publier vidéo motion design comme annonce officielle
  - Poster sur Reddit (subreddits sur tech, réseaux sociaux, privacy…)

---

### 🤖 Automatisation
- Utiliser Make ou Zapier pour publier automatiquement sur X + Mastodon + Facebook à partir d’un seul post
- Préparer 15 visuels/postes réutilisables (citations, astuces, screenshots)
  - Planifier la publication pour 1 mois (2-3 posts/semaine)

---

💡 **Mon conseil** :  
Ne parle pas juste de *Agora*, parle du **problème qu’Agora résout**  
> "Marre que les réseaux décident ce que vous voyez ? Agora vous rend le contrôle."
