<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$user = getUserById($_SESSION['user_id']);

require_once '../includes/header.php';
?>

<head>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/vis-network@9.1.2/standalone/umd/vis-network.min.js"></script>
    <style>
        :root {
            --primary: #ab9ff2;
            --primary-dark: #8a7de0;
            --accent: #2575fc;
            --success: #60d394;
            --error: #ee6055;
            --text: #333333;
            --text-light: #777777;
            --bg: #f8f9fa;
            --card-bg: #ffffff;
            --border: #e0e0e0;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header */
        .top {
            background: var(--card-bg);
            box-shadow: var(--shadow);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top h2 {
            color: var(--primary);
            text-align: center;
            margin: 0;
            font-size: 1.5rem;
        }

        /* Layout Principal */
        .main-container {
            display: flex;
            flex: 1;
            position: relative;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--card-bg);
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            padding: 20px;
            overflow-y: auto;
            transition: transform 0.3s ease;
            position: fixed;
            height: calc(100vh - 60px);
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            left: 10px;
            top: 100px;
            z-index: 101;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
        }

        /* Contenu Principal */
        .content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin 0.3s ease;
        }

        /* Upload Zone */
        .upload-dropzone {
            border: 2px dashed #6c757d;
            border-radius: 10px;
            padding: 40px 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            max-width: 250px;
            margin: 0 auto;
            box-shadow: var(--card-shadow);
        }

        .upload-dropzone i {
            color: #2575fc;
        }

        .upload-dropzone:hover,
        .upload-dropzone.dragover {
            border-color: #2575fc;
            background-color: rgba(13, 110, 253, 0.05);
            transform: translateY(-2px);
        }

        /* Mind Map Container */
        #mindMap {
            width: 100%;
            height: calc(100vh - 180px);
            min-height: 500px;
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        /* Outils */
        .tool-section {
            margin-bottom: 25px;
        }

        .tool-section h3 {
            font-size: 0.95rem;
            color: var(--text);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .color-palette {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .color-option {
            width: 100%;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .color-option.active {
            border-color: var(--text);
            transform: scale(1.05);
        }

        select,
        button {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--card-bg);
            color: var(--text);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        button {
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            background: var(--primary-dark);
        }

        button.secondary {
            background: var(--accent);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 100;
                height: calc(100vh - 60px);
                top: 60px;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }
        }

        /* Utilitaires */
        .hidden {
            display: none;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="top">
        <h2><i class="fas fa-project-diagram"></i> JSON Mind Mapper</h2>
    </div>

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fa-solid fa-gear"></i>
    </button>

    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="tool-section">
                <h3><i class="fas fa-palette"></i> Couleur Principale</h3>
                <div class="color-palette">
                    <div class="color-option active" style="background: var(--primary);" data-color="var(--primary)">
                    </div>
                    <div class="color-option" style="background: var(--accent);" data-color="var(--accent)"></div>
                    <div class="color-option" style="background: var(--success);" data-color="var(--success)"></div>
                    <div class="color-option" style="background: #ffd97d;" data-color="#ffd97d"></div>
                    <div class="color-option" style="background: #faaf72;" data-color="#faaf72"></div>
                    <div class="color-option" style="background: #ee6055;" data-color="#ee6055"></div>
                </div>
            </div>

            <div class="tool-section">
                <h3><i class="fas fa-shapes"></i> Forme des Nodes</h3>
                <select id="nodeShape">
                    <option value="box">Boîte</option>
                    <option value="ellipse">Ellipse</option>
                    <option value="diamond">Losange</option>
                    <option value="circle">Cercle</option>
                </select>
            </div>

            <div class="tool-section">
                <h3><i class="fas fa-project-diagram"></i> Type de Layout</h3>
                <select id="layoutType">
                    <option value="hierarchical">Hiérarchique</option>
                    <option value="standard">Standard</option>
                </select>
            </div>

            <div class="tool-section">
                <h3><i class="fas fa-arrows-alt"></i> Direction</h3>
                <select id="layoutDirection">
                    <option value="UD">Haut-Bas</option>
                    <option value="LR">Gauche-Droite</option>
                    <option value="DU">Bas-Haut</option>
                    <option value="RL">Droite-Gauche</option>
                </select>
            </div>

            <div class="tool-section">
                <h3><i class="fas fa-cog"></i> Options</h3>
                <button id="resetBtn">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
                <button id="exportBtn" class="secondary mt-2">
                    <i class="fas fa-download"></i> Exporter
                </button>
            </div>
        </div>

        <!-- Contenu Principal -->
        <div class="content" id="mainContent">
            <div class="upload-dropzone" id="dropZone">
                <i class="fas fa-file-upload fa-3x"></i>
                <p><strong>Déposez un fichier JSON ici</strong></p>
                <p class="text-light">Ou cliquez pour sélectionner</p>
                <input type="file" id="jsonUpload" accept=".json" class="hidden">
            </div>

            <div id="mindMapContainer" class="hidden">
                <div id="mindMap"></div>
            </div>
        </div>
    </div>

    <script>
        // Script pour le toggle de la sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('active');
        });
        // [Le même JavaScript que dans la version précédente]
        // (Conserve toutes les fonctionnalités existantes)
        let network;
        let allNodes = [];
        let allEdges = [];
        let currentColor = '#ab9ff2';

        const options = {
            nodes: {
                shape: 'box',
                color: {
                    background: currentColor,
                    border: '#6a0dad',
                    highlight: { background: currentColor }
                },
                font: { size: 14 },
                margin: 10
            },
            edges: {
                color: currentColor,
                smooth: true,
                arrows: { to: { enabled: true, scaleFactor: 0.5 } }
            },
            physics: {
                hierarchicalRepulsion: { nodeDistance: 120 }
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            initEventListeners();
        });

        function initEventListeners() {
            // Gestion upload
            document.getElementById('jsonUpload').addEventListener('change', handleFileSelect);
            document.getElementById('dropZone').addEventListener('click', () => document.getElementById('jsonUpload').click());

            // Drag & drop
            ['dragover', 'drop'].forEach(event => {
                document.getElementById('dropZone').addEventListener(event, e => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (event === 'dragover') {
                        e.currentTarget.style.background = 'rgba(171, 159, 242, 0.1)';
                    } else {
                        e.currentTarget.style.background = '';
                        handleFileSelect({ target: { files: e.dataTransfer.files } });
                    }
                });
            });

            // Outils
            document.querySelectorAll('.color-option').forEach(option => {
                option.addEventListener('click', function () {
                    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    currentColor = this.dataset.color.replace('var(--', '').replace(')', '');
                    updateNetworkStyle();
                });
            });

            document.getElementById('nodeShape').addEventListener('change', function () {
                options.nodes.shape = this.value;
                updateNetworkStyle();
            });

            document.getElementById('layoutType').addEventListener('change', function () {
                updateLayout();
            });

            document.getElementById('layoutDirection').addEventListener('change', function () {
                updateLayout();
            });

            // Boutons
            document.getElementById('resetBtn').addEventListener('click', resetMindMap);
            document.getElementById('exportBtn').addEventListener('click', exportAsPNG);
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file || !file.name.endsWith('.json')) {
                alert('Seuls les fichiers JSON sont acceptés !');
                return;
            }

            const reader = new FileReader();
            reader.onload = e => {
                try {
                    const json = JSON.parse(e.target.result);
                    generateMindMap(json);
                    document.getElementById('mindMapContainer').classList.remove('hidden');
                    document.getElementById('dropZone').classList.add('hidden');
                } catch (err) {
                    alert(`Erreur JSON : ${err.message}`);
                }
            };
            reader.readAsText(file);
        }

        function generateMindMap(data) {
            allNodes = [];
            allEdges = [];

            // Node racine
            allNodes.push({
                id: 1,
                label: 'RACINE',
                level: 0,
                color: { background: currentColor, border: '#6a0dad' },
                font: { color: 'white' },
                shape: options.nodes.shape
            });

            // Conversion récursive
            processNode(data, 1, 1);

            // Création du réseau
            if (network) network.destroy();
            network = new vis.Network(
                document.getElementById('mindMap'),
                { nodes: new vis.DataSet(allNodes), edges: new vis.DataSet(allEdges) },
                options
            );

            function processNode(obj, parentId, level) {
                Object.entries(obj).forEach(([key, value]) => {
                    const nodeId = allNodes.length + 1;
                    const isObject = typeof value === 'object' && value !== null;

                    allNodes.push({
                        id: nodeId,
                        label: isObject ? key : `${key}: ${formatValue(value)}`,
                        level: level,
                        color: { background: getNodeColor(level) },
                        shape: isObject ? options.nodes.shape : 'ellipse',
                        font: { size: 12 + (3 / level) }
                    });

                    allEdges.push({
                        from: parentId,
                        to: nodeId,
                        color: currentColor
                    });

                    if (isObject) processNode(value, nodeId, level + 1);
                });
            }
        }

        function updateNetworkStyle() {
            if (!network) return;

            // Mise à jour des couleurs
            options.nodes.color.background = currentColor;
            options.nodes.color.highlight.background = currentColor;
            options.edges.color = currentColor;

            // Mise à jour des nodes
            const updateNodes = allNodes.map(node => ({
                ...node,
                color: {
                    background: node.id === 1 ? currentColor : getNodeColor(node.level || 1),
                    border: node.id === 1 ? '#6a0dad' : currentColor
                },
                shape: node.id === 1 || node.shape === 'ellipse' ? node.shape : options.nodes.shape
            }));

            // Mise à jour des edges
            const updateEdges = allEdges.map(edge => ({
                ...edge,
                color: currentColor
            }));

            network.setOptions(options);
            network.body.data.nodes.update(updateNodes);
            network.body.data.edges.update(updateEdges);
        }

        function updateLayout() {
            if (!network) return;

            const layoutType = document.getElementById('layoutType').value;
            const direction = document.getElementById('layoutDirection').value;

            if (layoutType === 'hierarchical') {
                options.layout = {
                    hierarchical: {
                        direction: direction,
                        nodeSpacing: 120,
                        levelSeparation: 100
                    }
                };
                options.physics = { hierarchicalRepulsion: { nodeDistance: 140 } };
            } else {
                options.layout = { randomSeed: 42 };
                options.physics = {
                    barnesHut: {
                        gravitationalConstant: -2000,
                        centralGravity: 0.3
                    }
                };
            }

            network.setOptions(options);
            network.fit();
        }

        function getNodeColor(level) {
            const colors = [
                currentColor,
                '#2575fc',
                '#60d394',
                '#ffd97d',
                '#faaf72'
            ];
            return colors[level % colors.length];
        }

        function formatValue(value) {
            if (value === null) return 'null';
            if (Array.isArray(value)) return `[${value.length} éléments]`;
            if (typeof value === 'string') return value.length > 15 ? value.substring(0, 15) + '...' : value;
            return value;
        }

        function resetMindMap() {
            document.getElementById('mindMapContainer').classList.add('hidden');
            document.getElementById('dropZone').classList.remove('hidden');
            document.getElementById('jsonUpload').value = '';
            if (network) network.destroy();
        }

        function exportAsPNG() {
            if (!network) return;

            const canvas = document.querySelector('#mindMap canvas');
            const dataURL = canvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.download = 'mindmap-' + new Date().toISOString().slice(0, 10) + '.png';
            link.href = dataURL;
            link.click();
        }
    </script>

    <?php require_once '../includes/footer.php'; ?>