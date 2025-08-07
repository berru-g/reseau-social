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

<!--V2 with amchartjs 🫶-->
<div class="data-visualizer-header">
  <h1><i class="fas fa-chart-line"></i> Data Visualizer Pro</h1>
  <p>Transformez vos fichiers en insights visuels en 3 étapes</p>
  <div class="steps">
    <div class="step active">1 <span>Importer</span></div>
    <div class="step">2 <span>Visualiser</span></div>
    <div class="step">3 <span>Configurer</span></div>
  </div>
</div>

<div class="upload-container">
  <div class="upload-dropzone" id="dropZone">
    <i class="fas fa-cloud-upload-alt"></i>
    <p><strong>Déposez votre fichier ici</strong></p>
    <p>ou cliquez pour parcourir</p>
    <div class="file-types mt-2">
      <span class="badge badge-csv">.csv</span>
      <span class="badge badge-excel">.xlsx</span>
      <span class="badge badge-json">.json</span>
    </div>
    <input type="file" id="dataUpload" accept=".csv,.json,.xls,.xlsx" hidden>
  </div>
</div>

<div class="container mt-5">
  <div id="columnSelector" class="mb-4"></div>
  
  <!-- Graphique Container avec options de style -->
  <div id="chartdiv" style="width: 100%; height: 500px;"></div>
  
  <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
    <select id="chartType" class="form-select w-auto">
      <option value="column">Colonnes</option>
      <option value="bar">Barres</option>
      <option value="line">Lignes</option>
      <option value="pie">Camembert</option>
      <option value="donut">Donut</option>
      <option value="radar">Radar</option>
      <option value="xy">XY (Nuage de points)</option>
      
    </select>
    
    <select id="chartTheme" class="form-select w-auto">
      <option value="am4themes_animated">Animé</option>
      <option value="am4themes_dark">Sombre</option>
      <option value="am4themes_dataviz">DataViz</option>
      <option value="am4themes_material">Material</option>
      <option value="am4themes_kelly">Kelly</option>
      <option value="am4themes_frozen">Frozen</option>
    </select>
    
    <button id="exportPNG" class="btn btn-primary"><i class="fas fa-image"></i> PNG</button>
    <button id="exportPDF" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> PDF</button>
    <button id="exportCSV" class="btn btn-success"><i class="fas fa-file-csv"></i> CSV</button>
  </div>

  <div class="table-responsive mt-4">
    <table id="dataTable" class="table table-bordered table-striped"></table>
  </div>

  <div class="mt-4">
    <h5>📁 Format de fichier idéal (CSV/Excel)</h5>
    <p>Assurez-vous que votre fichier respecte ce format de base :</p>
    <pre>
Produit,Quantité,Ventes
Pommes,120,1500
Bananes,90,1200
Poires,60,800
    </pre>
    <p>💡 Colonne 1 : catégorie / Colonne 2 : valeur numérique / Colonne 3+ : séries supplémentaires</p>
  </div>
</div>

<link rel="stylesheet" href="../assets/css/data-to-chart.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
  .upload-dropzone {
    border: 2px dashed #6c757d;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
  }
  .upload-dropzone:hover, .upload-dropzone.dragover {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
  }
  .steps {
    display: flex;
    justify-content: center;
    margin: 20px 0;
  }
  .step {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 10px;
    font-weight: bold;
    position: relative;
  }
  .step.active {
    background: #0d6efd;
    color: white;
  }
  .step span {
    position: absolute;
    bottom: -25px;
    font-size: 12px;
    font-weight: normal;
    white-space: nowrap;
  }
  .badge {
    margin: 0 5px;
  }
  .badge-csv { background: #20c997; }
  .badge-excel { background: #198754; }
  .badge-json { background: #6f42c1; }
  #chartdiv {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  }
</style>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>

<!-- amCharts -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/dark.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/dataviz.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/material.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/kelly.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/frozen.js"></script>

<script>
  // Références aux éléments DOM
  const dropZone = document.getElementById("dropZone");
  const fileInput = document.getElementById("dataUpload");
  const columnSelector = document.getElementById("columnSelector");
  const chartTypeSelect = document.getElementById("chartType");
  const chartThemeSelect = document.getElementById("chartTheme");
  const dataTable = document.getElementById("dataTable");
  
  // Variables d'état
  let currentData = [];
  let currentFields = [];
  let currentChart = null;
  let currentTheme = null;

  // Initialisation amCharts
  am4core.useTheme(am4themes_animated);
  currentTheme = am4themes_animated;
  
  // Gestion des événements
  dropZone.addEventListener("click", () => fileInput.click());
  dropZone.addEventListener("dragover", e => { e.preventDefault(); dropZone.classList.add("dragover"); });
  dropZone.addEventListener("dragleave", () => dropZone.classList.remove("dragover"));
  dropZone.addEventListener("drop", e => {
    e.preventDefault();
    dropZone.classList.remove("dragover");
    handleFile(e.dataTransfer.files[0]);
  });
  fileInput.addEventListener("change", e => handleFile(e.target.files[0]));
  
  chartTypeSelect.addEventListener("change", updateChart);
  chartThemeSelect.addEventListener("change", updateTheme);
  
  document.getElementById("exportPNG").addEventListener("click", exportPNG);
  document.getElementById("exportPDF").addEventListener("click", exportPDF);
  document.getElementById("exportCSV").addEventListener("click", exportCSV);

  // Fonctions principales
  function handleFile(file) {
    if (!file) return;
    
    const reader = new FileReader();
    const ext = file.name.split(".").pop().toLowerCase();
    
    if (ext === "csv") {
      reader.onload = e => parseCSV(e.target.result);
      reader.readAsText(file);
    } else if (["xls", "xlsx"].includes(ext)) {
      reader.onload = e => {
        const wb = XLSX.read(e.target.result, { type: "binary" });
        const ws = wb.Sheets[wb.SheetNames[0]];
        const csv = XLSX.utils.sheet_to_csv(ws);
        parseCSV(csv);
      };
      reader.readAsBinaryString(file);
    } else if (ext === "json") {
      reader.onload = e => parseJSON(e.target.result);
      reader.readAsText(file);
    }
  }

  function parseJSON(jsonString) {
    try {
      const data = JSON.parse(jsonString);
      if (!Array.isArray(data)) throw new Error("JSON doit être un tableau de lignes.");
      
      currentFields = Object.keys(data[0]);
      currentData = data.filter(row => row[currentFields[0]]);
      
      updateColumnSelector();
      renderTable();
      updateChart();
    } catch (err) {
      alert("Erreur lors de l'analyse du JSON : " + err.message);
    }
  }

  function parseCSV(csv) {
    const result = Papa.parse(csv, { header: true });
    currentFields = result.meta.fields;
    currentData = result.data.filter(row => row[currentFields[0]]);
    
    updateColumnSelector();
    renderTable();
    updateChart();
  }

  function updateColumnSelector() {
    if (!currentFields || currentFields.length < 2) return;
    
    columnSelector.innerHTML = `
      <div class="mb-3">
        <label class="form-label">Colonne de catégories (axe X)</label>
        <select id="categoryField" class="form-select">
          ${currentFields.map(f => `<option value="${f}" ${f === currentFields[0] ? 'selected' : ''}>${f}</option>`).join("")}
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Colonnes de valeurs (séries)</label>
        ${currentFields.slice(1).map(f => `
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="${f}" id="series-${f}" checked>
            <label class="form-check-label" for="series-${f}">${f}</label>
          </div>
        `).join("")}
      </div>
    `;
    
    document.getElementById("categoryField").addEventListener("change", updateChart);
    columnSelector.querySelectorAll("input[type='checkbox']").forEach(cb => {
      cb.addEventListener("change", updateChart);
    });
  }

  function updateTheme() {
    const themeName = chartThemeSelect.value;
    const theme = am4themes[themeName];
    
    if (theme) {
      am4core.unuseTheme(currentTheme);
      am4core.useTheme(theme);
      currentTheme = theme;
      
      if (currentChart) {
        currentChart.dispose();
        updateChart();
      }
    }
  }

  function updateChart() {
    if (!currentData.length || !currentFields.length) return;
    
    // Détruire le graphique existant
    if (currentChart) {
      currentChart.dispose();
    }
    
    // Récupérer les sélections
    const categoryField = document.getElementById("categoryField").value;
    const selectedSeries = Array.from(columnSelector.querySelectorAll("input[type='checkbox']:checked")).map(cb => cb.value);
    
    if (selectedSeries.length === 0) {
      alert("Veuillez sélectionner au moins une série de données");
      return;
    }
    
    const chartType = chartTypeSelect.value;
    
    // Créer le nouveau graphique
    const chart = am4core.create("chartdiv", am4charts.XYChart);
    currentChart = chart;
    
    // Configurer les données
    chart.data = currentData.map(row => {
      const item = { category: row[categoryField] };
      selectedSeries.forEach(series => {
        item[series] = parseFloat(row[series]) || 0;
      });
      return item;
    });
    
    // Créer les axes
    const categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "category";
    categoryAxis.renderer.grid.template.location = 0;
    categoryAxis.renderer.minGridDistance = 30;
    
    const valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    
    // Créer les séries en fonction du type de graphique
    selectedSeries.forEach(series => {
      let seriesInstance;
      
      switch(chartType) {
        case "column":
          seriesInstance = chart.series.push(new am4charts.ColumnSeries());
          break;
        case "bar":
          seriesInstance = chart.series.push(new am4charts.ColumnSeries());
          // Pour un bar chart, on inverse les axes
          chart.xAxes.getIndex(0).renderer.opposite = true;
          chart.yAxes.getIndex(0).renderer.opposite = true;
          break;
        case "line":
          seriesInstance = chart.series.push(new am4charts.LineSeries());
          seriesInstance.strokeWidth = 3;
          seriesInstance.tensionX = 0.8;
          break;
        case "pie":
          // Pour un pie chart, on crée un graphique différent
          const pieChart = am4core.create("chartdiv", am4charts.PieChart);
          pieChart.data = currentData.map(row => ({
            category: row[categoryField],
            value: parseFloat(row[series]) || 0
          }));
          
          const pieSeries = pieChart.series.push(new am4charts.PieSeries());
          pieSeries.dataFields.value = "value";
          pieSeries.dataFields.category = "category";
          pieSeries.slices.template.stroke = am4core.color("#fff");
          pieSeries.slices.template.strokeWidth = 2;
          pieSeries.slices.template.strokeOpacity = 1;
          
          pieSeries.labels.template.disabled = true;
          pieSeries.ticks.template.disabled = true;
          
          pieChart.legend = new am4charts.Legend();
          currentChart = pieChart;
          return;
        case "donut":
          const donutChart = am4core.create("chartdiv", am4charts.PieChart);
          donutChart.data = currentData.map(row => ({
            category: row[categoryField],
            value: parseFloat(row[series]) || 0
          }));
          
          const donutSeries = donutChart.series.push(new am4charts.PieSeries());
          donutSeries.dataFields.value = "value";
          donutSeries.dataFields.category = "category";
          donutSeries.slices.template.stroke = am4core.color("#fff");
          donutSeries.slices.template.strokeWidth = 2;
          donutSeries.slices.template.strokeOpacity = 1;
          donutSeries.innerRadius = am4core.percent(50);
          
          donutSeries.labels.template.disabled = true;
          donutSeries.ticks.template.disabled = true;
          
          donutChart.legend = new am4charts.Legend();
          currentChart = donutChart;
          return;
        case "radar":
          const radarChart = am4core.create("chartdiv", am4charts.RadarChart);
          radarChart.data = currentData.map(row => ({
            category: row[categoryField],
            value: parseFloat(row[series]) || 0
          }));
          
          const categoryAxisRadar = radarChart.xAxes.push(new am4charts.CategoryAxis());
          categoryAxisRadar.dataFields.category = "category";
          
          const valueAxisRadar = radarChart.yAxes.push(new am4charts.ValueAxis());
          
          const radarSeries = radarChart.series.push(new am4charts.RadarSeries());
          radarSeries.dataFields.valueY = "value";
          radarSeries.dataFields.categoryX = "category";
          radarSeries.name = series;
          radarSeries.strokeWidth = 3;
          radarSeries.tensionX = 0.8;
          
          radarChart.legend = new am4charts.Legend();
          currentChart = radarChart;
          return;
        case "xy":
          seriesInstance = chart.series.push(new am4charts.LineSeries());
          seriesInstance.strokeWidth = 2;
          seriesInstance.bullets.push(new am4charts.CircleBullet());
          break;
        default:
          seriesInstance = chart.series.push(new am4charts.ColumnSeries());
      }
      
      seriesInstance.dataFields.valueY = series;
      seriesInstance.dataFields.categoryX = "category";
      seriesInstance.name = series;
      
      if (chartType === "bar") {
        seriesInstance.dataFields.valueX = series;
        seriesInstance.dataFields.categoryY = "category";
      }
      
      // Configurer le tooltip
      seriesInstance.tooltipText = "{name}: [bold]{valueY}[/]";
      
      // Animation
      seriesInstance.sequencedInterpolation = true;
      seriesInstance.defaultState.transitionDuration = 1000;
    });
    
    // Ajouter la légende
    chart.legend = new am4charts.Legend();
    
    // Ajouter le curseur
    if (["column", "bar", "line", "xy"].includes(chartType)) {
      chart.cursor = new am4charts.XYCursor();
      chart.cursor.lineY.opacity = 0;
    }
    
    // Ajouter le défilement
    if (currentData.length > 10) {
      chart.scrollbarX = new am4core.Scrollbar();
    }
  }

  function renderTable() {
    if (!currentData.length) return;
    
    const thead = `<thead><tr>${currentFields.map(h => `<th>${h}</th>`).join("")}</tr></thead>`;
    const tbody = `<tbody>${currentData.map(r => `<tr>${currentFields.map(h => `<td>${r[h]}</td>`).join("")}</tr>`).join("")}</tbody>`;
    dataTable.innerHTML = thead + tbody;
  }

  function exportPNG() {
    if (currentChart) {
      currentChart.exporting.export("png");
    }
  }

  function exportPDF() {
    if (currentChart) {
      currentChart.exporting.export("pdf");
    }
  }

  function exportCSV() {
    if (!currentData.length) return;
    
    let csv = Papa.unparse({
      fields: currentFields,
      data: currentData
    });
    
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    
    link.setAttribute("href", url);
    link.setAttribute("download", "data_export.csv");
    link.style.visibility = "hidden";
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
</script>
<?php require_once '../includes/footer.php'; ?>