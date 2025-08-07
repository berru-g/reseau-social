-- Création de la base de données
CREATE DATABASE IF NOT EXISTS messagerie_collegues;
USE messagerie_collegues;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des commentaires
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des likes
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    comment_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, comment_id)
);


-- table des uploads csv/xlsx/json
CREATE TABLE user_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('csv', 'excel', 'json', 'googlesheet') NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- permettre le partage des uploads
ALTER TABLE user_files 
ADD COLUMN is_public BOOLEAN DEFAULT FALSE,
ADD COLUMN share_token VARCHAR(32) NULL;


-- table des uploads img png/jpg
CREATE TABLE user_files_img (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('png', 'jpg', 'pdf', 'mp4') NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Pour permettre les réponses sous les post du mur
ALTER TABLE comments
ADD COLUMN parent_id INT NULL,
ADD COLUMN file_path VARCHAR(255) NULL,
ADD COLUMN file_type ENUM('image', 'video') NULL,
ADD FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE;

-- Profile user
-- user V1.2
ALTER TABLE users
ADD COLUMN profile_picture VARCHAR(255) NULL,
ADD COLUMN website_url VARCHAR(255) NULL;

-- afficher img profile du propriétaire ( pas encore push dans la bdd )
--SELECT f.*, u.id as owner_id, u.username, u.profile_picture, u.email
--FROM user_files f
--JOIN users u ON f.user_id = u.id
--WHERE f.id = ? -- bug pas l

-- Ajout des format img à la gallery upload
ALTER TABLE user_files MODIFY COLUMN file_type ENUM('csv','excel','json','image','googlesheet') NOT NULL;


-- Les features de mon https://crypto-free-tools.netlify.app

CREATE TABLE user_crypto_holdings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crypto_id VARCHAR(50) NOT NULL,
    crypto_name VARCHAR(100) NOT NULL,
    purchase_price DECIMAL(20, 6) NOT NULL,
    quantity DECIMAL(20, 6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- dashboard amchartjs et historique

CREATE TABLE wallet_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_value DECIMAL(20, 6) NOT NULL,
    snapshot_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_snapshot (user_id, snapshot_date)
);
