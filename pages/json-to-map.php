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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/jsonto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/vis-network@9.1.2/standalone/umd/vis-network.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsoneditor@9.9.0/dist/jsoneditor.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/jsoneditor@9.9.0/dist/jsoneditor.min.css" rel="stylesheet">
 
</head>

<body>
    <section class="tool-header">
        <div class="tool-header-container">
            <h2><i class="fas fa-diagram-project"></i> JSON Visualizer Pro</h2>
            <div class="header-actions">
                <button class="outline" id="toggleEditorBtn">
                    <i class="fas fa-code"></i> Éditeur JSON
                </button>
            </div>
        </div>
    </section>

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-sliders-h"></i>
    </button>

    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="tool-section">
                <h3><i class="fas fa-upload"></i> Importation</h3>
                <input type="text" id="jsonUrl" placeholder="URL de l'api" class="mb-4">
                <button id="loadFromUrlBtn" class="secondary">
                    <i class="fas fa-cloud-download-alt"></i> Charger depuis URL
                </button>
            </div>

            <div class="tool-section">
                <h3><i class="fas fa-palette"></i> Personnalisation</h3>
                <div class="color-palette">
                    <div class="color-option active" style="background: #a395f2ff;" data-color="#9685f6ff"></div>
                    <div class="color-option" style="background: #3b82f6;" data-color="#3b82f6"></div>
                    <div class="color-option" style="background: #60d394;;" data-color="#60d394;"></div>
                    <div class="color-option" style="background: #ffd97d;" data-color="#ffd97d"></div>
                    <div class="color-option" style="background: #ef4444;" data-color="#ef4444"></div>
                    <div class="color-option" style="background: #ab9ff2;" data-color="#ab9ff2"></div>
                    <div class="color-option" style="background: #fcd5dc;" data-color="#fcd5dc"></div>
                    <div class="color-option" style="background: #3ad38b;" data-color="#3ad38b"></div>
                    <div class="color-option" style="background: #ff7243;" data-color="#ff7243"></div>
                    <div class="color-option" style="background: #64748b;" data-color="#64748b"></div>
                </div>
            </div>

            <div class="tool-section">
                <h3><i class="fas fa-sliders-h"></i> Options de visualisation</h3>
                <label for="nodeShape">Forme des nœuds</label>
                <select id="nodeShape">
                    <option value="box">Boîte</option>
                    <option value="ellipse">Ellipse</option>
                    <option value="diamond">Losange</option>
                    <option value="circle">Cercle</option>
                    <option value="database">Base de données</option>
                    <option value="image">Image</option>
                </select>

                <label for="layoutType">Type de disposition</label>
                <select id="layoutType">
                    <option value="hierarchical">Hiérarchique</option>
                    <option value="standard">Standard</option>
                    <option value="circular">Circulaire</option>
                </select>

                <label for="layoutDirection">Direction</label>
                <select id="layoutDirection">
                    <option value="UD">Haut-Bas</option>
                    <option value="LR">Gauche-Droite</option>
                    <option value="DU">Bas-Haut</option>
                    <option value="RL">Droite-Gauche</option>
                </select>
            </div>

            <div class="tool-section">
                <h3><i class="fas fa-tools"></i> Actions</h3>
                <button id="resetBtn" class="outline">
                    <i class="fas fa-trash-alt"></i> Réinitialiser
                </button>
                <div class="editor-toolbar-actions mt-2">
                    <button id="exportPngBtn" class="secondary">
                        <i class="fas fa-image"></i> Exporter PNG
                    </button>
                    <button id="exportJsonBtn">
                        <i class="fas fa-file-export"></i> Exporter JSON
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content" id="mainContent">
            <div class="upload-container" id="uploadContainer">
                <div class="upload-dropzone" id="dropZone">
                    <i class="fas fa-file-upload"></i>
                    <h3>Déposez un fichier JSON ici</h3>
                    <p>Ou cliquez pour sélectionner un fichier</p>
                    <input type="file" id="jsonUpload" accept=".json,application/json">
                </div>
                <div class="upload-options">
                    <button id="sampleDataBtn" class="outline">
                        <i class="fas fa-vial"></i> Charger un exemple
                    </button>
                    <button id="pasteJsonBtn">
                        <i class="fas fa-paste"></i> Coller du JSON
                    </button>
                </div>
            </div>

            <div id="visualizationArea" class="hidden">
                <div class="tabs">
                    <div class="tab active" data-view="mindmap">Mind Map</div>
                    <div class="tab" data-view="editor">Éditeur JSON</div>
                    <div class="tab" data-view="tree">Arbre</div>
                    <div class="tab" data-view="graph">Graphique</div>
                </div>

                <div class="visualization-container">
                    <!-- Mind Map View -->
                    <div id="mindmap-view" class="view-container">
                        <div id="mindMap"></div>
                    </div>

                    <!-- JSON Editor View -->
                    <div id="editor-view" class="view-container hidden">
                        <div id="jsoneditor"></div>
                    </div>

                    <!-- Tree View -->
                    <div id="tree-view" class="view-container hidden">
                        <div id="treeDiagram"></div>
                    </div>

                    <!-- Graph View -->
                    <div id="graph-view" class="view-container hidden">
                        <canvas id="graphChart"></canvas>
                    </div>

                    <div class="loading-overlay hidden" id="loadingOverlay">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="notification" id="notification"></div>

    <script>
        // Configuration globale
        let network, editor, chart;
        let allNodes = [], allEdges = [];
        let currentData = null;
        let currentColor = '#ab9ff2';
        let currentView = 'mindmap';

        // Options par défaut pour vis-network
        const options = {
            nodes: {
                shape: 'box',
                color: {
                    background: currentColor,
                    border: '#2575fc',
                    highlight: {
                        background: currentColor,
                        border: '#2575fc'
                    }
                },
                font: {
                    size: 14,
                    face: 'Inter',
                    color: '#111827'
                },
                margin: 10,
                borderWidth: 2,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.1)',
                    size: 10,
                    x: 0,
                    y: 2
                }
            },
            edges: {
                color: currentColor,
                smooth: {
                    type: 'continuous',
                    roundness: 0.5
                },
                arrows: {
                    to: {
                        enabled: true,
                        scaleFactor: 0.6,
                        type: 'arrow'
                    }
                },
                width: 2,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.1)',
                    size: 5,
                    x: 0,
                    y: 1
                }
            },
            physics: {
                hierarchicalRepulsion: {
                    nodeDistance: 140,
                    springLength: 100,
                    springConstant: 0.01,
                    damping: 0.09
                }
            },
            interaction: {
                hover: true,
                tooltipDelay: 200,
                hideEdgesOnDrag: true,
                multiselect: true
            },
            layout: {
                hierarchical: {
                    direction: 'UD',
                    nodeSpacing: 120,
                    levelSeparation: 100,
                    sortMethod: 'directed'
                }
            }
        };

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            initEventListeners();
            initJSONEditor();
        });

        // Fonctions principales
        function initJSONEditor() {
            const container = document.getElementById('jsoneditor');
            editor = new JSONEditor(container, {
                mode: 'tree',
                modes: ['tree', 'view', 'form', 'code', 'text'],
                onError: (err) => {
                    showNotification('Erreur JSON: ' + err.message, 'error');
                },
                onModeChange: (newMode, oldMode) => {
                    console.log('Mode switched from', oldMode, 'to', newMode);
                }
            });
        }

        function generateVisualization(data, viewType = currentView) {
            if (!data) return;

            showLoading(true);
            currentData = data;

            try {
                // Mettre à jour l'éditeur JSON
                editor.set(data);

                // Cacher toutes les vues
                hideAllViews();

                // Afficher la vue sélectionnée
                document.getElementById(`${viewType}-view`).classList.remove('hidden');
                document.querySelector(`.tab[data-view="${viewType}"]`).classList.add('active');

                // Générer la visualisation appropriée
                switch (viewType) {
                    case 'mindmap':
                        createMindMap(data);
                        break;
                    case 'editor':
                        // L'éditeur est déjà mis à jour
                        break;
                    case 'tree':
                        createTreeView(data);
                        break;
                    case 'graph':
                        createGraphView(data);
                        break;
                }

                // Afficher la zone de visualisation
                document.getElementById('uploadContainer').classList.add('hidden');
                document.getElementById('visualizationArea').classList.remove('hidden');

                showNotification('Visualisation générée avec succès', 'success');
            } catch (err) {
                showNotification('Erreur lors de la génération: ' + err.message, 'error');
                console.error(err);
            } finally {
                showLoading(false);
            }
        }

        function createMindMap(data) {
            // Nettoyer les données précédentes
            allNodes = [];
            allEdges = [];

            if (network) {
                network.destroy();
            }

            // Créer le nœud racine
            const rootId = 1;
            allNodes.push({
                id: rootId,
                label: 'ROOT',
                level: 0,
                color: {
                    background: currentColor,
                    border: '#4f46e5',
                    highlight: {
                        background: currentColor,
                        border: '#4338ca'
                    }
                },
                font: {
                    color: 'white',
                    size: 16,
                    face: 'Inter',
                    bold: true
                },
                shape: document.getElementById('nodeShape').value,
                size: 25,
                borderWidth: 2
            });

            // Traiter les données récursivement
            processNode(data, rootId, 1);

            // Créer le réseau
            const container = document.getElementById('mindMap');
            network = new vis.Network(
                container,
                { nodes: new vis.DataSet(allNodes), edges: new vis.DataSet(allEdges) },
                options
            );

            // Configurer les événements du réseau
            network.on('click', (params) => {
                if (params.nodes.length) {
                    const nodeId = params.nodes[0];
                    const node = allNodes.find(n => n.id === nodeId);
                    network.selectNodes([nodeId]);
                }
            });

            // Appliquer la disposition
            updateLayout();
        }

        function createTreeView(data) {
            // Implémentation simplifiée - à améliorer
            document.getElementById('treeDiagram').innerHTML = generateTreeHTML(data);
        }

        function createGraphView(data) {
            const ctx = document.getElementById('graphChart').getContext('2d');

            if (chart) {
                chart.destroy();
            }

            // Exemple simple - à adapter selon vos besoins
            const labels = Object.keys(data);
            const values = labels.map(key => {
                const val = data[key];
                if (typeof val === 'object') {
                    return Object.keys(val).length;
                }
                return 1;
            });

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Éléments',
                        data: values,
                        backgroundColor: labels.map((_, i) =>
                            `hsl(${(i * 360 / labels.length)}, 70%, 60%)`
                        ),
                        borderColor: labels.map((_, i) =>
                            `hsl(${(i * 360 / labels.length)}, 70%, 40%)`
                        ),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return `${context.label}: ${context.raw} élément(s)`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Fonctions utilitaires
        function processNode(obj, parentId, level) {
            if (!obj || typeof obj !== 'object') return;

            Object.entries(obj).forEach(([key, value], index) => {
                const nodeId = allNodes.length + 1;
                const isObject = typeof value === 'object' && value !== null;
                const isArray = Array.isArray(value);

                // Déterminer la forme en fonction du type
                let shape = document.getElementById('nodeShape').value;
                if (isArray) shape = 'box';
                if (!isObject && !isArray) shape = 'ellipse';

                // Créer le nœud
                allNodes.push({
                    id: nodeId,
                    label: isObject ? key : `${key}: ${formatValue(value)}`,
                    level: level,
                    color: {
                        background: getNodeColor(level),
                        border: darkenColor(getNodeColor(level), 20),
                        highlight: {
                            background: lightenColor(getNodeColor(level), 10),
                            border: darkenColor(getNodeColor(level), 30)
                        }
                    },
                    shape: shape,
                    font: {
                        size: 14 - (level * 0.5),
                        face: 'Inter'
                    },
                    margin: 8,
                    borderWidth: 1
                });

                // Créer le lien
                allEdges.push({
                    from: parentId,
                    to: nodeId,
                    color: {
                        color: currentColor,
                        highlight: lightenColor(currentColor, 20),
                        hover: lightenColor(currentColor, 20)
                    },
                    width: 1.5,
                    smooth: {
                        type: 'continuous',
                        roundness: 0.3
                    }
                });

                // Traiter récursivement les objets/tableaux
                if (isObject) {
                    processNode(value, nodeId, level + 1);
                }
            });
        }

        function generateTreeHTML(data, level = 0) {
            if (!data || typeof data !== 'object') return '';

            const isArray = Array.isArray(data);
            const keys = Object.keys(data);

            let html = `<ul class="tree-level-${level}">`;

            keys.forEach(key => {
                const value = data[key];
                const isObject = typeof value === 'object' && value !== null;

                html += `<li>
                    <span class="tree-node ${isObject ? 'has-children' : ''}">
                        ${key}${!isObject ? `: ${formatValue(value)}` : ''}
                    </span>`;

                if (isObject) {
                    html += generateTreeHTML(value, level + 1);
                }

                html += `</li>`;
            });

            html += `</ul>`;
            return html;
        }

        function hideAllViews() {
            document.querySelectorAll('.view-container').forEach(view => {
                view.classList.add('hidden');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
        }

        function resetVisualization() {
            hideAllViews();
            document.getElementById('uploadContainer').classList.remove('hidden');
            document.getElementById('visualizationArea').classList.add('hidden');
            document.getElementById('jsonUpload').value = '';
            document.getElementById('jsonUrl').value = '';

            if (network) {
                network.destroy();
                network = null;
            }

            if (chart) {
                chart.destroy();
                chart = null;
            }

            currentData = null;
            allNodes = [];
            allEdges = [];

            showNotification('Visualisation réinitialisée', 'info');
        }

        function updateNetworkStyle() {
            if (!network) return;

            // Mettre à jour les options globales
            options.nodes.color.background = currentColor;
            options.nodes.color.highlight.background = currentColor;
            options.edges.color = currentColor;

            // Mettre à jour le réseau
            network.setOptions(options);

            // Mettre à jour les nœuds existants
            const updates = allNodes.map(node => {
                const isRoot = node.id === 1;
                return {
                    id: node.id,
                    color: {
                        background: isRoot ? currentColor : getNodeColor(node.level || 1),
                        border: isRoot ? darkenColor(currentColor, 20) : darkenColor(getNodeColor(node.level || 1), 20),
                        highlight: {
                            background: isRoot ? currentColor : lightenColor(getNodeColor(node.level || 1), 10),
                            border: isRoot ? darkenColor(currentColor, 30) : darkenColor(getNodeColor(node.level || 1), 30)
                        }
                    },
                    shape: document.getElementById('nodeShape').value
                };
            });

            network.body.data.nodes.update(updates);

            // Mettre à jour les liens
            const edgeUpdates = allEdges.map(edge => ({
                id: edge.id,
                color: {
                    color: currentColor,
                    highlight: lightenColor(currentColor, 20),
                    hover: lightenColor(currentColor, 20)
                }
            }));

            network.body.data.edges.update(edgeUpdates);
        }

        function updateLayout() {
            if (!network) return;

            const type = document.getElementById('layoutType').value;
            const dir = document.getElementById('layoutDirection').value;

            options.layout = type === 'hierarchical' ? {
                hierarchical: {
                    direction: dir,
                    nodeSpacing: 120,
                    levelSeparation: 100,
                    sortMethod: 'directed'
                }
            } : type === 'circular' ? {
                randomSeed: 42,
                improvedLayout: true
            } : {
                randomSeed: 42
            };

            options.physics = type === 'hierarchical' ? {
                hierarchicalRepulsion: {
                    nodeDistance: 140,
                    springLength: 100,
                    springConstant: 0.01,
                    damping: 0.09
                }
            } : {
                barnesHut: {
                    gravitationalConstant: -2000,
                    centralGravity: 0.3,
                    springLength: 200,
                    springConstant: 0.04,
                    damping: 0.09
                }
            };

            network.setOptions(options);
            network.fit();
        }

        function exportAsPNG() {
            if (!network) return;

            const canvas = document.querySelector('#mindMap canvas');
            if (!canvas) return;

            const link = document.createElement('a');
            link.download = `agora-dataviz.com-${new Date().toISOString().slice(0, 10)}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();

            showNotification('Export PNG terminé', 'success');
        }

        function exportAsJSON() {
            if (!currentData) return;

            const dataStr = JSON.stringify(currentData, null, 2);
            const blob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.download = `json-data-${new Date().toISOString().slice(0, 10)}.json`;
            link.href = url;
            link.click();

            showNotification('Export JSON terminé', 'success');
        }

        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.toggle('hidden', !show);
        }

        function showNotification(message, type = 'info') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification';

            // Appliquer le style en fonction du type
            switch (type) {
                case 'success':
                    notification.style.background = '#60d394;';
                    break;
                case 'error':
                    notification.style.background = '#ee6055';
                    break;
                case 'warning':
                    notification.style.background = '#ffd97d';
                    break;
                default:
                    notification.style.background = '#3b82f6';
            }

            notification.classList.add('show');

            // Masquer après 3 secondes
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Fonctions d'aide
        function getNodeColor(level) {
            const colors = [
                '#3b82f6', // blue
                '#60d394;', // green
                '#ffd97d', // yellow
                '#fcd5dc', // pink
                '#ab9ff2', // purple
                '#3ad38b', // teal
                '#f99b58ff', // orange
                '#64748b'  // gray
            ];
            return colors[level % colors.length] || currentColor;
        }

        function lightenColor(color, percent) {
            // Simplifié - en production utiliser une librairie de couleur
            return color;
        }

        function darkenColor(color, percent) {
            // Simplifié - en production utiliser une librairie de couleur
            return color;
        }

        function formatValue(value) {
            if (value === null) return 'null';
            if (value === undefined) return 'undefined';
            if (typeof value === 'boolean') return value ? 'true' : 'false';
            if (Array.isArray(value)) return `[array: ${value.length} items]`;
            if (typeof value === 'object') return `{object: ${Object.keys(value).length} keys}`;
            if (typeof value === 'string') {
                return value.length > 20 ? `"${value.substring(0, 20)}..."` : `"${value}"`;
            }
            return value;
        }

        function loadSampleData() {
            const sampleData = {
                "name": "John Doe",
                "age": 35,
                "isActive": true,
                "address": {
                    "street": "123 Main St",
                    "city": "Anytown",
                    "zip": "12345",
                    "coordinates": {
                        "lat": 40.7128,
                        "lng": -74.0060
                    }
                },
                "contacts": [
                    {
                        "type": "email",
                        "value": "john@example.com"
                    },
                    {
                        "type": "phone",
                        "value": "+1 555-1234"
                    }
                ],
                "projects": {
                    "completed": 12,
                    "ongoing": 3,
                    "planned": 5
                }
            };

            generateVisualization(sampleData);
        }

        function loadFromUrl(url) {
            if (!url) {
                showNotification('Veuillez entrer une URL valide', 'error');
                return;
            }

            showLoading(true);

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.json();
                })
                .then(data => {
                    generateVisualization(data);
                    showNotification('Données chargées depuis URL', 'success');
                })
                .catch(err => {
                    showNotification('Erreur: ' + err.message, 'error');
                    console.error(err);
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        function showPasteDialog() {
            const jsonText = prompt("Collez votre JSON ici:");
            if (!jsonText) return;

            try {
                const jsonData = JSON.parse(jsonText);
                generateVisualization(jsonData);
            } catch (err) {
                showNotification('JSON invalide: ' + err.message, 'error');
            }
        }

        // Gestionnaires d'événements
        function initEventListeners() {
            // Gestion des fichiers
            document.getElementById('jsonUpload').addEventListener('change', handleFileSelect);
            document.getElementById('dropZone').addEventListener('click', () => document.getElementById('jsonUpload').click());

            // Drag & drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                document.getElementById('dropZone').addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                document.getElementById('dropZone').addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                document.getElementById('dropZone').addEventListener(eventName, unhighlight, false);
            });

            document.getElementById('dropZone').addEventListener('drop', handleDrop, false);

            // Contrôles de la sidebar
            document.querySelectorAll('.color-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    document.querySelector('.color-option.active').classList.remove('active');
                    opt.classList.add('active');
                    currentColor = opt.dataset.color;
                    updateNetworkStyle();
                });
            });

            document.getElementById('nodeShape').addEventListener('change', () => {
                options.nodes.shape = document.getElementById('nodeShape').value;
                updateNetworkStyle();
            });

            document.getElementById('layoutType').addEventListener('change', updateLayout);
            document.getElementById('layoutDirection').addEventListener('change', updateLayout);

            // Boutons
            document.getElementById('resetBtn').addEventListener('click', resetVisualization);
            document.getElementById('exportPngBtn').addEventListener('click', exportAsPNG);
            document.getElementById('exportJsonBtn').addEventListener('click', exportAsJSON);
            document.getElementById('loadFromUrlBtn').addEventListener('click', () => {
                loadFromUrl(document.getElementById('jsonUrl').value);
            });
            document.getElementById('sampleDataBtn').addEventListener('click', loadSampleData);
            document.getElementById('pasteJsonBtn').addEventListener('click', showPasteDialog);

            // Onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    const viewType = tab.dataset.view;
                    currentView = viewType;
                    generateVisualization(currentData, viewType);
                });
            });

            // Toggle sidebar
            document.getElementById('sidebarToggle').addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('active');
            });

            // Toggle editor
            document.getElementById('toggleEditorBtn').addEventListener('click', () => {
                if (currentView === 'editor') {
                    currentView = 'mindmap';
                } else {
                    currentView = 'editor';
                }
                generateVisualization(currentData, currentView);
            });
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight() {
            document.getElementById('dropZone').classList.add('dragover');
        }

        function unhighlight() {
            document.getElementById('dropZone').classList.remove('dragover');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length) {
                document.getElementById('jsonUpload').files = files;
                handleFileSelect({ target: { files } });
            }
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.endsWith('.json') && !file.type.includes('json')) {
                showNotification('Seuls les fichiers JSON sont acceptés', 'error');
                return;
            }

            showLoading(true);

            const reader = new FileReader();
            reader.onload = e => {
                try {
                    const jsonData = JSON.parse(e.target.result);
                    generateVisualization(jsonData);
                } catch (err) {
                    showNotification('Erreur JSON: ' + err.message, 'error');
                    console.error(err);
                } finally {
                    showLoading(false);
                }
            };
            reader.onerror = () => {
                showNotification('Erreur de lecture du fichier', 'error');
                showLoading(false);
            };
            reader.readAsText(file);
        }
    </script>
</body>

</html>