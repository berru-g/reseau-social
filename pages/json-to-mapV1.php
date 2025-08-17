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
<!--V2 en cour ... -->
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
        .data-visualizer-header {
            text-align: center;
            padding: 20px 0;
            background: white;
            margin-top: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 10;
        }

        .data-visualizer-header h2 {
            font-weight: 700;
            color: #fff;
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
            max-width: 350px;
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
    <div class="data-visualizer-header">
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
                <h3><i class="fas fa-palette"></i> Format</h3>
                <select id="visual-format">
                    <option value="mindmap">Mind Map</option>
                    <option value="orgchart">Organigramme</option>
                    <option value="kanban">Tableau Kanban</option>
                    <option value="timeline">Chronologie</option>
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

            <div id="visualization-container">
                <!-- Mind Map (actif par défaut) -->
                <div id="mindmap-view" class="view-container" style="display: none;">
                    <div id="mindMap"></div>
                </div>
                <!-- Organigramme -->
                <div id="orgchart-view" class="view-container" style="display: none;"></div>
                <!-- Kanban -->
                <div id="kanban-view" class="view-container" style="display: none;"></div>
                <!-- Chronologie -->
                <div id="timeline-view" class="view-container" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script>
        // Script pour le toggle de la sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('active');
        });
        // ============= CONFIG =============
        let network, allNodes = [], allEdges = [], currentData = null;
        let currentColor = '#ab9ff2';

        const options = {
            nodes: {
                shape: 'box',
                color: { background: currentColor, border: '#6a0dad', highlight: { background: currentColor } },
                font: { size: 14 },
                margin: 10
            },
            edges: {
                color: currentColor,
                smooth: true,
                arrows: { to: { enabled: true, scaleFactor: 0.5 } }
            },
            physics: { hierarchicalRepulsion: { nodeDistance: 120 } }
        };

        // ============= INIT =============
        document.addEventListener('DOMContentLoaded', () => {
            initEventListeners();
            resetVisualization(); // Initialise l'état correctement
        });

        // ============= CORE FUNCTIONS =============
        function generateVisualization(data, format = 'mindmap') {
            if (!data) return;

            // Cleanup
            if (network) network.destroy();
            hideAllViews();

            // Show selected view
            const container = document.getElementById(`${format}-view`);
            container.style.display = 'block';

            switch (format) {
                case 'mindmap':
                    createMindMap(data, container);
                    break;
                case 'orgchart':
                    createOrgChart(data, container);
                    break;
                case 'kanban':
                    createKanban(data, container);
                    break;
                case 'timeline':
                    createTimeline(data, container);
                    break;
            }

            document.getElementById('dropZone').classList.add('hidden');
        }

        function createMindMap(data, container) {
            allNodes = [{
                id: 1,
                label: 'RACINE',
                level: 0,
                color: { background: currentColor, border: '#6a0dad' },
                font: { color: 'white' },
                shape: options.nodes.shape
            }];

            processNode(data, 1, 1);

            container.innerHTML = '<div id="mindMap"></div>';
            network = new vis.Network(
                document.getElementById('mindMap'),
                { nodes: new vis.DataSet(allNodes), edges: new vis.DataSet(allEdges) },
                options
            );
        }

        function createOrgChart(data, container) {
            const rootKey = Object.keys(data)[0];
            container.innerHTML = `
    <div class="orgchart">
      <div class="level root" style="background:${currentColor}">${rootKey}</div>
      ${generateOrgLevels(data[rootKey])}
    </div>
  `;
            addStyle('orgchart-styles', `
    .orgchart { display: flex; flex-direction: column; align-items: center; gap: 20px; }
    .level { padding: 10px 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .root { color: white; font-weight: bold; }
  `);
        }

        function createKanban(data, container) {
            container.innerHTML = `
    <div class="kanban-board">
      ${['À faire', 'En cours', 'Terminé'].map(col => `
        <div class="kanban-column" style="border-top: 3px solid ${currentColor}">
          <h3>${col}</h3>
          ${Object.keys(data).map(key => `<div class="kanban-card">${key}</div>`).join('')}
        </div>
      `).join('')}
    </div>
  `;
            addStyle('kanban-styles', `
    .kanban-board { display: flex; gap: 15px; padding: 20px; }
    .kanban-column { flex: 1; background: #f8f9fa; padding: 15px; border-radius: 5px; }
    .kanban-card { background: white; margin: 10px 0; padding: 10px; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  `);
        }

        function createTimeline(data, container) {
            container.innerHTML = `
    <div class="timeline">
      ${Object.keys(data).map(year => `
        <div class="timeline-item">
          <div class="timeline-year" style="background:${currentColor}">${year}</div>
          <div class="timeline-content">${Object.keys(data[year]).join(', ')}</div>
        </div>
      `).join('')}
    </div>
  `;
            addStyle('timeline-styles', `
    .timeline { display: flex; padding: 20px; overflow-x: auto; }
    .timeline-item { display: flex; flex-direction: column; align-items: center; min-width: 150px; }
    .timeline-year { color: white; padding: 5px 10px; border-radius: 20px; margin-bottom: 10px; }
  `);
        }

        // ============= UTILITIES =============
        function hideAllViews() {
            document.querySelectorAll('.view-container').forEach(view => {
                view.style.display = 'none';
                view.innerHTML = '';
            });
        }

        function resetVisualization() {
            hideAllViews();
            document.getElementById('dropZone').classList.remove('hidden');
            document.getElementById('jsonUpload').value = '';
            if (network) network.destroy();
            currentData = null;
            removeStyles(['orgchart-styles', 'kanban-styles', 'timeline-styles']);
        }

        function addStyle(id, css) {
            removeStyle(id);
            const style = document.createElement('style');
            style.id = id;
            style.textContent = css;
            document.head.appendChild(style);
        }

        function removeStyles(ids) {
            ids.forEach(id => removeStyle(id));
        }

        function removeStyle(id) {
            const style = document.getElementById(id);
            if (style) style.remove();
        }

        // ============= EVENT HANDLERS =============
        function initEventListeners() {
            // File handling
            document.getElementById('jsonUpload').addEventListener('change', handleFileSelect);
            document.getElementById('dropZone').addEventListener('click', () => document.getElementById('jsonUpload').click());

            // Drag & drop
            ['dragover', 'drop'].forEach(event => {
                document.getElementById('dropZone').addEventListener(event, e => {
                    e.preventDefault();
                    e.stopPropagation();
                    e.currentTarget.style.background = event === 'dragover' ? 'rgba(171, 159, 242, 0.1)' : '';
                    if (event === 'drop') handleFileSelect({ target: { files: e.dataTransfer.files } });
                });
            });

            // Sidebar controls
            document.querySelectorAll('.color-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    currentColor = opt.dataset.color.replace('var(--', '').replace(')', '');
                    updateNetworkStyle();
                });
            });

            document.getElementById('nodeShape').addEventListener('change', () => {
                options.nodes.shape = this.value;
                updateNetworkStyle();
            });

            document.getElementById('layoutType').addEventListener('change', updateLayout);
            document.getElementById('layoutDirection').addEventListener('change', updateLayout);
            document.getElementById('visual-format').addEventListener('change', handleFormatChange);

            // Buttons
            document.getElementById('resetBtn').addEventListener('click', resetVisualization);
            document.getElementById('exportBtn').addEventListener('click', exportAsPNG);
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file?.name.endsWith('.json')) return alert('Seuls les fichiers JSON sont acceptés');

            const reader = new FileReader();
            reader.onload = e => {
                try {
                    currentData = JSON.parse(e.target.result);
                    generateVisualization(currentData, document.getElementById('visual-format').value);
                } catch (err) {
                    alert(`Erreur JSON : ${err.message}`);
                }
            };
            reader.readAsText(file);
        }

        function handleFormatChange() {
            if (currentData) generateVisualization(currentData, this.value);
        }

        // ============= VIS.JS HELPERS =============
        function updateNetworkStyle() {
            if (!network) return;

            options.nodes.color.background = currentColor;
            options.nodes.color.highlight.background = currentColor;
            options.edges.color = currentColor;

            network.setOptions(options);
            network.body.data.nodes.update(allNodes.map(node => ({
                ...node,
                color: {
                    background: node.id === 1 ? currentColor : getNodeColor(node.level || 1),
                    border: node.id === 1 ? '#6a0dad' : currentColor
                },
                shape: node.id === 1 || node.shape === 'ellipse' ? node.shape : options.nodes.shape
            })));
        }

        function updateLayout() {
            if (!network) return;

            const type = document.getElementById('layoutType').value;
            const dir = document.getElementById('layoutDirection').value;

            options.layout = type === 'hierarchical' ? {
                hierarchical: { direction: dir, nodeSpacing: 120, levelSeparation: 100 }
            } : { randomSeed: 42 };

            options.physics = type === 'hierarchical'
                ? { hierarchicalRepulsion: { nodeDistance: 140 } }
                : { barnesHut: { gravitationalConstant: -2000, centralGravity: 0.3 } };

            network.setOptions(options);
            network.fit();
        }

        function exportAsPNG() {
            if (!network) return;
            const link = document.createElement('a');
            link.download = `visualization-${new Date().toISOString().slice(0, 10)}.png`;
            link.href = document.querySelector('#mindMap canvas').toDataURL('image/png');
            link.click();
        }

        // ============= DATA PROCESSING =============
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

                allEdges.push({ from: parentId, to: nodeId, color: currentColor });
                if (isObject) processNode(value, nodeId, level + 1);
            });
        }

        function getNodeColor(level) {
            return ['#2575fc', '#60d394', '#ffd97d', '#faaf72'][level % 4] || currentColor;
        }

        function formatValue(value) {
            if (value === null) return 'null';
            if (Array.isArray(value)) return `[${value.length} éléments]`;
            return typeof value === 'string'
                ? value.length > 15 ? `${value.substring(0, 15)}...` : value
                : value;
        }
    </script>