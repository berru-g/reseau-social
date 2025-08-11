<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$file_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$file = getFileById($file_id);

if (!$file || !canAccessFile($_SESSION['user_id'], $file_id)) {
    die("Accès refusé ou fichier non trouvé");
}

$file_path = '../uploads/' . $file['file_path'];
$file_ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
$user = getUserById($_SESSION['user_id']);
$owner = getUserById($file['owner_id']);

require_once '../includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/data-to-chart.css">
<div class="data-visualizer-header">
    <h1><i class="fas fa-chart-line"></i> Data Visualizer</h1>
    <p><i class="fas fa-file"></i> <?= htmlspecialchars($file['file_name']) ?></p>
    <?php if ($owner): ?>
        <p>
            <i class="fas fa-user"></i>
            <a href="<?= BASE_URL ?>/pages/profile.php?user_id=<?= $owner['id'] ?>" class="owner-link">
                <?= htmlspecialchars($owner['username'] ?? $owner['email']) ?>
            </a>
        </p>
    <?php endif; ?>
    <!--btn retour exactement là où on en etais via historyback protège contre l'injection-->
    <a href="#" class="primary-btn back-btn" data-fallback="search.php">
        <i class="fa-solid fa-reply"></i>
    </a>
</div>

<div class="container mt-5">
    <div id="columnSelector" class="mb-4"></div>
    <div id="chartdiv" style="width: 100%; height: 500px;"></div>
    <div class="mt-4 d-flex flex-wrap gap-2">
        <select id="chartType" class="form-select w-auto">
            <option value="column">Colonnes</option>
            <option value="bar">Barres</option>
            <option value="line">Lignes</option>
            <option value="pie">Camembert</option>
            <option value="donut">Donut</option>
            <option value="radar">Radar</option>
            <option value="stackedColumn">Colonnes Empilées</option>
        </select>
        <button id="exportPNG" class="btn btn-primary"><i class="fas fa-image"></i> PNG</button>
        <button id="exportPDF" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> PDF</button>
        <button id="exportCSV" class="btn btn-success"><i class="fas fa-file-csv"></i> CSV</button>
    </div>

    <div class="table-responsive mt-4">
        <table id="dataTable" class="table table-bordered table-striped"></table>
    </div>
</div>

<!-- Dépendances amCharts -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/dark.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/material.js"></script>

<!-- Autres dépendances -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation amCharts
    am4core.useTheme(am4themes_animated);
    am4core.options.autoDispose = true;
    
    let currentChart = null;
    let currentData = [];
    let currentFields = [];
    
    // Chargement du fichier
    loadFile();
    
    // Gestion du retour
    document.querySelectorAll('.back-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (document.referrer.includes(window.location.hostname)) {
                history.back();
            } else {
                window.location.href = btn.dataset.fallback;
            }
        });
    });
    
    function loadFile() {
        const fileExt = "<?= $file_ext ?>";
        const filePath = "<?= $file_path ?>";
        
        fetch(filePath)
            .then(response => {
                if (fileExt === 'csv') return response.text();
                if (fileExt === 'json') return response.json();
                if (fileExt === 'xlsx' || fileExt === 'xls') {
                    return response.arrayBuffer().then(buffer => {
                        const wb = XLSX.read(buffer, { type: "array" });
                        return XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]]);
                    });
                }
                throw new Error("Format non supporté");
            })
            .then(data => {
                if (fileExt === 'csv') {
                    parseCSV(data);
                } else {
                    parseJSON(data);
                }
            })
            .catch(error => {
                console.error("Erreur de chargement:", error);
                alert("Erreur lors du chargement du fichier: " + error.message);
            });
    }
    
    function parseJSON(data) {
        try {
            if (!Array.isArray(data)) throw new Error("Format JSON invalide");
            
            currentFields = Object.keys(data[0]);
            currentData = data.filter(row => {
                return currentFields.every(field => row[field] !== undefined);
            });
            
            updateUI();
        } catch (err) {
            alert("Erreur JSON: " + err.message);
        }
    }
    
    function parseCSV(csv) {
        Papa.parse(csv, {
            header: true,
            skipEmptyLines: true,
            complete: function(results) {
                currentFields = results.meta.fields || [];
                currentData = results.data.filter(row => {
                    return currentFields.every(field => row[field] !== undefined);
                });
                updateUI();
            },
            error: function(err) {
                alert("Erreur CSV: " + err.message);
            }
        });
    }
    
    function updateUI() {
        updateColumnSelector();
        renderTable();
        updateChart();
        
        // Écouteurs d'événements
        document.getElementById('chartType').addEventListener('change', updateChart);
        document.getElementById('exportPNG').addEventListener('click', exportPNG);
        document.getElementById('exportPDF').addEventListener('click', exportPDF);
        document.getElementById('exportCSV').addEventListener('click', exportCSV);
    }
    
    function updateColumnSelector() {
        const container = document.getElementById('columnSelector');
        container.innerHTML = '';
        
        if (currentFields.length < 1) return;
        
        // Sélecteur de catégorie
        const categoryDiv = document.createElement('div');
        categoryDiv.className = 'mb-3';
        categoryDiv.innerHTML = `
            <label class="form-label">Colonne Catégorie (X)</label>
            <select id="categoryField" class="form-select">
                ${currentFields.map(f => `<option value="${f}">${f}</option>`).join('')}
            </select>
        `;
        container.appendChild(categoryDiv);
        
        // Sélecteur de valeurs
        const valuesDiv = document.createElement('div');
        valuesDiv.className = 'mb-3';
        valuesDiv.innerHTML = `
            <label class="form-label">Colonnes Valeurs</label>
            ${currentFields.map(f => `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="val-${f}" value="${f}" ${currentFields.indexOf(f) > 0 ? 'checked' : ''}>
                    <label class="form-check-label" for="val-${f}">${f}</label>
                </div>
            `).join('')}
        `;
        container.appendChild(valuesDiv);
        
        // Écouteurs
        container.querySelectorAll('select, input').forEach(el => {
            el.addEventListener('change', updateChart);
        });
    }
    
    function getSelectedFields() {
        const category = document.getElementById('categoryField').value;
        const values = Array.from(document.querySelectorAll('#columnSelector input:checked')).map(el => el.value);
        return { category, values };
    }
    
    function updateChart() {
        if (currentChart) {
            currentChart.dispose();
        }
        
        const { category, values } = getSelectedFields();
        if (values.length === 0) return;
        
        const chartType = document.getElementById('chartType').value;
        
        // Création du graphique selon le type
        switch(chartType) {
            case 'pie':
            case 'donut':
                createPieChart(category, values[0]);
                break;
            case 'radar':
                createRadarChart(category, values);
                break;
            default:
                createXYChart(chartType, category, values);
        }
    }
    
    function createXYChart(type, category, values) {
        const chart = am4core.create("chartdiv", am4charts.XYChart);
        currentChart = chart;
        
        // Préparation des données
        chart.data = currentData.map(row => {
            const item = { category: row[category] };
            values.forEach(val => {
                item[val] = parseFloat(row[val]) || 0;
            });
            return item;
        });
        
        // Création des axes
        const xAxis = chart.xAxes.push(
            type.includes('bar') ? new am4charts.ValueAxis() : new am4charts.CategoryAxis()
        );
        xAxis.dataFields.category = "category";
        
        const yAxis = chart.yAxes.push(new am4charts.ValueAxis());
        
        // Inversion pour les barres
        if (type.includes('bar')) {
            chart.xAxes.getIndex(0).renderer.opposite = true;
            chart.yAxes.getIndex(0).renderer.opposite = true;
        }
        
        // Création des séries
        values.forEach(valueField => {
            let series;
            
            if (type.includes('line')) {
                series = chart.series.push(new am4charts.LineSeries());
                series.strokeWidth = 3;
            } else {
                series = chart.series.push(new am4charts.ColumnSeries());
                if (type.includes('stacked')) {
                    series.stacked = true;
                }
            }
            
            series.dataFields.valueY = valueField;
            series.dataFields.categoryX = "category";
            series.name = valueField;
            
            if (type.includes('bar')) {
                series.dataFields.valueX = valueField;
                series.dataFields.categoryY = "category";
            }
        });
        
        // Légende
        chart.legend = new am4charts.Legend();
        
        // Curseur
        chart.cursor = new am4charts.XYCursor();
    }
    
    function createPieChart(category, value) {
        const chart = am4core.create("chartdiv", am4charts.PieChart);
        currentChart = chart;
        
        chart.data = currentData.map(row => ({
            category: row[category],
            value: parseFloat(row[value]) || 0
        }));
        
        const series = chart.series.push(new am4charts.PieSeries());
        series.dataFields.value = "value";
        series.dataFields.category = "category";
        series.slices.template.stroke = am4core.color("#fff");
        series.slices.template.strokeWidth = 2;
        
        if (document.getElementById('chartType').value === 'donut') {
            series.innerRadius = am4core.percent(50);
        }
        
        chart.legend = new am4charts.Legend();
    }
    
    function createRadarChart(category, values) {
        const chart = am4core.create("chartdiv", am4charts.RadarChart);
        currentChart = chart;
        
        chart.data = currentData.map(row => {
            const item = { category: row[category] };
            values.forEach(val => {
                item[val] = parseFloat(row[val]) || 0;
            });
            return item;
        });
        
        const categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "category";
        
        const valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
        
        values.forEach(valueField => {
            const series = chart.series.push(new am4charts.RadarSeries());
            series.dataFields.valueY = valueField;
            series.dataFields.categoryX = "category";
            series.name = valueField;
            series.strokeWidth = 3;
        });
        
        chart.legend = new am4charts.Legend();
    }
    
    function renderTable() {
        const table = document.getElementById('dataTable');
        if (currentData.length === 0) return;
        
        // En-têtes
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        currentFields.forEach(field => {
            const th = document.createElement('th');
            th.textContent = field;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        
        // Données
        const tbody = document.createElement('tbody');
        currentData.forEach(row => {
            const tr = document.createElement('tr');
            currentFields.forEach(field => {
                const td = document.createElement('td');
                td.textContent = row[field] || '';
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        
        table.innerHTML = '';
        table.appendChild(thead);
        table.appendChild(tbody);
    }
    
    function exportPNG() {
        if (currentChart) {
            currentChart.exporting.export('png');
        }
    }
    
    function exportPDF() {
        if (currentChart) {
            currentChart.exporting.export('pdf');
        }
    }
    
    function exportCSV() {
        const csv = Papa.unparse({
            fields: currentFields,
            data: currentData
        });
        
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'export.csv';
        link.click();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>