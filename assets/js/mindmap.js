document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('jsonUpload');
    const mindMapContainer = document.getElementById('mindMapContainer');

    // Gestion de l'upload
    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));
    dropZone.addEventListener('dragover', (e) => e.preventDefault());
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        handleFile(e.dataTransfer.files[0]);
    });

    function handleFile(file) {
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const json = JSON.parse(e.target.result);
                renderMindMap(json);
                mindMapContainer.style.display = 'block';
                dropZone.style.display = 'none';
            } catch (err) {
                alert("JSON invalide ! Erreur : " + err.message);
            }
        };
        reader.readAsText(file);
    }

    function renderMindMap(json) {
        const container = document.getElementById('mindMap');
        const options = {
            layout: {
                hierarchical: {
                    direction: 'UD', // Up-Down
                    sortMethod: 'directed'
                }
            },
            nodes: {
                shape: 'box',
                margin: 10,
                font: {
                    size: 14
                }
            },
            edges: {
                smooth: true,
                arrows: 'to'
            },
            physics: {
                enabled: true,
                hierarchicalRepulsion: {
                    nodeDistance: 120
                }
            }
        };

        // Conversion du JSON en nodes/edges pour Vis.js
        const { nodes, edges } = convertJsonToMindMap(json);
        new vis.Network(container, { nodes, edges }, options);
    }

    function convertJsonToMindMap(data, parentId = null, nodes = [], edges = [], level = 0) {
        const id = Math.random().toString(36).substr(2, 9);

        // Node racine
        if (parentId === null) {
            nodes.push({
                id,
                label: 'Root',
                level: 0,
                color: '#6a0dad',
                font: { color: 'white' }
            });
        }

        // Parcours récursif
        if (typeof data === 'object' && data !== null) {
            Object.entries(data).forEach(([key, value]) => {
                const childId = Math.random().toString(36).substr(2, 9);
                const isObject = typeof value === 'object' && value !== null;

                nodes.push({
                    id: childId,
                    label: isObject ? key : `${key}: ${value}`,
                    level: level + 1,
                    color: isObject ? '#2575fc' : '#60d394'
                });

                edges.push({
                    from: parentId || id,
                    to: childId
                });

                if (isObject) {
                    convertJsonToMindMap(value, childId, nodes, edges, level + 1);
                }
            });
        }

        return { nodes, edges };
    }
});