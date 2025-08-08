<?php
require __DIR__.'/includes/config.php';
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/functions.php';

function countLinesInProject($dir, $extensions = ['php', 'js', 'html', 'css']) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $totalLines = 0;
    $details = [];

    foreach ($rii as $file) {
        if ($file->isDir()) continue;

        $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        if (!in_array($ext, $extensions)) continue;

        $lines = count(file($file->getPathname()));
        $totalLines += $lines;
        $details[$ext] = ($details[$ext] ?? 0) + $lines;
    }

    return [$totalLines, $details];
}

list($total, $byType) = countLinesInProject(__DIR__);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Projet</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #ddd;
            color: #808080;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 960px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .card {
            background: #F1F1F1;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 0 20px rgba(0, 26, 255, 0.19);
            animation: fadeInUp 0.7s ease;
        }

        .card h2 {
            margin-top: 0;
            color: #2575fc;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .stat {
            flex: 1 1 45%;
            margin-bottom: 20px;
        }

        .stat i {
            font-size: 2rem;
            color: #ab9ff2;
        }

        .stat strong {
            display: block;
            margin-top: 10px;
            font-size: 1.2rem;
        }

        canvas {
            margin-top: 30px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #999;
            font-size: 0.9rem;
        }

        .footer a {
            color: #fff;
            text-decoration: none;
        }

        .total-lines {
            text-align: center;
            font-size: 1.4rem;
            margin-top: 30px;
            color: #60d394;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>✅ Agora V 1.4 – Check Système</h2>
            <p>Tous les includes fonctionnent parfaitement.</p>
            <p><strong>Date :</strong> 2025-08-07</p>
        </div>

        <div class="card">
            <h2>📊 Lignes de code</h2>
            <div class="stats">
                <?php foreach ($byType as $ext => $count): ?>
                    <div class="stat">
                        <i class="fas fa-file-code"></i>
                        <strong>.<?= $ext ?> : <?= $count ?> lignes</strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="total-lines">
                <i class="fas fa-code"></i> Total : <strong><?= $total ?> lignes</strong>
            </div>
            <canvas id="codeChart"></canvas>
        </div>

        <div class="footer">
            Conçu avec ❤️ par <a href="https://gael-berru.com" target="_blank">Gael Berru</a>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('codeChart').getContext('2d');
        const codeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($byType)) ?>,
                datasets: [{
                    label: 'Lignes de code',
                    data: <?= json_encode(array_values($byType)) ?>,
                    backgroundColor: '#ab9ff2'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#fff' },
                        grid: { color: '#444' }
                    },
                    x: {
                        ticks: { color: '#fff' },
                        grid: { color: '#444' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#fff' }
                    }
                }
            }
        });
    </script>
</body>
</html>
