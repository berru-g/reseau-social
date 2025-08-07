<?php
require __DIR__.'/includes/config.php';
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/functions.php';

echo "Agora V 1.4";


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test de liaison</title>
    <style>
        body {
            background: #0e0e0e;
            color: #f0f0f0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .status {
            background: #1a1a1a;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(255, 0, 90, 0.3);
            text-align: center;
        }

        .status h1 {
            margin-bottom: 10px;
            color: #4fff93;
        }

        .status p {
            margin: 5px 0;
            font-size: 1.1rem;
        }

        .signature {
            margin-top: 30px;
            font-size: 0.9rem;
            color: #999;
        }

        .signature a {
            color: #fff;
            text-decoration: none;
        }

        .signature a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="status">
        <h1>✅ Test réussi</h1>
        <p>Tous les includes fonctionnent parfaitement.</p>
        <p>Agora <strong>V 1.4 au 2025-08-07</strong></p>
    </div>
    <div class="signature">
        Conçu avec ❤️ par <a href="https://gael-berru.com" target="_blank">Gael Berru</a>
    </div>
</body>
</html>
