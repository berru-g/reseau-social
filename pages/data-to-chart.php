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

<!--V3 🫶-->
<!-- Enhanced Data Visualizer 3.0 -->
<div class="data-visualizer-header">
  <h1><i class="fas fa-chart-network"></i> Data Visualizer 3.0</h1>
  <p>Transformez vos données en insights avancés avec IA</p>
  <div class="steps">
    <div class="step active">1 <span>Importer</span></div>
    <div class="step">2 <span>Analyser</span></div>
    <div class="step">3 <span>Visualiser</span></div>
    <div class="step">4 <span>Exporter</span></div>
  </div>
</div>

<div class="upload-container">
  <div class="upload-dropzone" id="dropZone">
    <i class="fas fa-cloud-upload-alt fa-3x"></i>
    <p><strong>Déposez votre fichier ici</strong></p>
    <p class="text-muted">Supporte CSV, Excel, JSON et APIs</p>
    <div class="file-types mt-2">
      <span class="badge badge-csv">.csv</span>
      <span class="badge badge-excel">.xlsx</span>
      <span class="badge badge-json">.json</span>
      <span class="badge badge-api">API</span>
    </div>
    <input type="file" id="dataUpload" accept=".csv,.json,.xls,.xlsx,.xml" hidden>
  </div>

  <div id="apiConnection" class="mt-4" style="display:none;">
    <div class="input-group mb-3">
      <input type="text" id="apiEndpoint" class="form-control" placeholder="Endpoint URL">
      <button class="btn" id="fetchApiData">Connecter</button>
    </div>
    <div id="apiParams"></div>
  </div>
</div>

<div class="container mt-5">
  <div class="row">
    <div class="col-md-3">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-sliders-h"></i> Configuration
        </div>
        <div class="card-body" id="columnSelector">
          <!-- Dynamically filled -->
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header">
          <i class="fas fa-chart-pie"></i> Type de Visualisation
        </div>
        <div class="card-body">
          <select id="chartType" class="form-select mb-3">
            <optgroup label="Basique">
              <option value="column">Colonnes</option>
              <option value="bar">Barres</option>
              <option value="line">Lignes</option>
            </optgroup>
            <optgroup label="Avancé">
              <option value="pie">Camembert 3D</option>
              <option value="donut">Donut 3D</option>
              <option value="radar">Radar</option>
              <option value="xy">Nuage 3D</option>
              <option value="heatmap">Heatmap</option>
              <option value="treemap">Treemap</option>
              <option value="network">Réseau</option>
              <option value="sankey">Sankey</option>
            </optgroup>
          </select>

          <select id="chartTheme" class="form-select mb-3">
            <option value="am4themes_animated">Animé</option>
            <option value="am4themes_dark">Sombre</option>
            <option value="am4themes_material">Material</option>
            <option value="am4themes_dataviz">DataViz</option>
            <option value="am4themes_amcharts">AmCharts</option>
          </select>

          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="enable3D" checked>
            <label class="form-check-label" for="enable3D">Mode 3D</label>
          </div>

          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="enableAnimations" checked>
            <label class="form-check-label" for="enableAnimations">Animations</label>
          </div>

          <button id="autoDetect" class="btn btn-sm btn-outline-primary w-100 mb-2">
            <i class="fas fa-magic"></i> Auto-détection
          </button>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header">
          <i class="fas fa-brain"></i> Analyse IA
        </div>
        <div class="card-body">
          <button id="analyzeTrends" class="btn btn-sm btn-success w-100 mb-2">
            <i class="fas fa-chart-line"></i> Détecter tendances
          </button>
          <button id="findOutliers" class="btn btn-sm btn-warning w-100 mb-2">
            <i class="fas fa-exclamation-triangle"></i> Trouver anomalies
          </button>
          <button id="predictValues" class="btn btn-sm btn-info w-100">
            <i class="fas fa-crystal-ball"></i> Prédire valeurs
          </button>
        </div>
      </div>
    </div>

    <div class="col-md-9">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="fas fa-chart-area"></i> Visualisation</span>
          <div>
            <button id="exportPNG" class="btn"><i class="fas fa-image"></i> PNG</button>
            <button id="exportPDF" class="btn"><i class="fas fa-file-pdf"></i> PDF</button>
            <button id="exportCSV" class="btn"><i class="fas fa-file-csv"></i> CSV</button>
            <button id="exportJSON" class="btn"><i class="fas fa-file-code"></i>
              JSON</button>
          </div>
        </div>
        <div class="card-body">
          <!-- Graphique Container avec options de style -->
          <div id="chartdiv" style="width: 100%; height: 500px;"></div>

          <!-- Analyse Container -->
          <div id="analysisResults" class="mt-3" style="display:none;">
            <div class="alert alert-info">
              <h5><i class="fas fa-robot"></i> Insights IA</h5>
              <div id="aiInsights"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header">
          <i class="fas fa-table"></i> Données brutes
          <span class="badge bg-primary float-end" id="rowCount">0 lignes</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped mb-0"></table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Data Cleaning Modal -->
<div class="modal fade" id="cleaningModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-broom"></i> Nettoyage des données</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Colonnes</h6>
            <div id="columnCleaner" class="mb-3"></div>
          </div>
          <div class="col-md-6">
            <h6>Valeurs manquantes</h6>
            <div class="mb-3">
              <select id="missingValuesStrategy" class="form-select">
                <option value="remove">Supprimer les lignes</option>
                <option value="mean">Remplacer par la moyenne</option>
                <option value="median">Remplacer par la médiane</option>
                <option value="zero">Remplacer par 0</option>
              </select>
            </div>

            <h6>Transformations</h6>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="normalizeData">
              <label class="form-check-label" for="normalizeData">Normaliser les données</label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="logTransform">
              <label class="form-check-label" for="logTransform">Transformation logarithmique</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn" id="applyCleaning">Appliquer</button>
      </div>
    </div>
  </div>
</div>

<!-- Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
<style>
  :root {
    --primary-bg: #f8f9fa;
    --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  }

  body {
    background-color: var(--primary-bg);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

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

  .steps {
    display: flex;
    justify-content: center;
    margin: 20px 0;
    gap: 30px;
  }

  .step {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    position: relative;
    box-shadow: var(--card-shadow);
  }

  .step.active {
    background: linear-gradient(135deg, #2575fc, #ab9ff2);
    color: white;
    transform: scale(1.1);
  }

  .step span {
    position: absolute;
    bottom: -25px;
    font-size: 12px;
    font-weight: normal;
    white-space: nowrap;
  }

  .badge {
    margin: 0 3px;
    font-weight: normal;
  }

  .badge-csv {
    background: #ffd97d;
  }

  .badge-excel {
    background: #60d394;
  }

  .badge-json {
    background: #ab9ff2;
  }

  .badge-api {
    background: #faaf72ff;
  }

  #chartdiv {
    background: white;
    border-radius: 8px;
    box-shadow: var(--card-shadow);
    transition: all 0.3s;
  }

  .card {
    border: none;
    border-radius: 10px;
    box-shadow: var(--card-shadow);
    margin-bottom: 20px;
  }

  .card-header {
    border-radius: 10px 10px 0 0 !important;
    font-weight: 600;
    background-color: #ab9ff2;
  }

  .form-switch .form-check-input {
    width: 2.5em;
    height: 1.5em;
  }

  .data-visualizer-header {
    text-align: center;
    padding: 20px 0;
    background: white;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  }

  .data-visualizer-header h1 {
    font-weight: 700;
    color: #333;
  }

  #dataTable th {
    background-color: #f8f9fa;
    position: sticky;
    top: 0;
  }

  .highlight-cell {
    animation: highlight 1.5s;
  }
  .btn {
    color: white;
  }

  @keyframes highlight {
    0% {
      background-color: #fff3cd;
    }

    100% {
      background-color: transparent;
    }
  }

  .ai-trend {
    color: #ab9ff2;
    font-weight: bold;
  }

  .ai-outlier {
    color: #ee6055;
    font-weight: bold;
  }

  .ai-prediction {
    color: #60d394;
    font-weight: bold;
  }
</style>

<script src="/assets/js/data-to-chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.2/echarts.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.2/extension/bmap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.4.2/chroma.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Références aux éléments DOM
  const dropZone = document.getElementById("dropZone");
  const fileInput = document.getElementById("dataUpload");
  const columnSelector = document.getElementById("columnSelector");
  const chartTypeSelect = document.getElementById("chartType");
  const chartThemeSelect = document.getElementById("chartTheme");
  const dataTable = document.getElementById("dataTable");
  const rowCount = document.getElementById("rowCount");
  const apiConnection = document.getElementById("apiConnection");
  const fetchApiData = document.getElementById("fetchApiData");
  const apiEndpoint = document.getElementById("apiEndpoint");
  const enable3D = document.getElementById("enable3D");
  const enableAnimations = document.getElementById("enableAnimations");
  const analyzeTrends = document.getElementById("analyzeTrends");
  const findOutliers = document.getElementById("findOutliers");
  const predictValues = document.getElementById("predictValues");
  const analysisResults = document.getElementById("analysisResults");
  const aiInsights = document.getElementById("aiInsights");
  const autoDetect = document.getElementById("autoDetect");
  const cleaningModal = new bootstrap.Modal('#cleaningModal');
  const applyCleaning = document.getElementById("applyCleaning");

  // Variables d'état
  let currentData = [];
  let originalData = [];
  let currentFields = [];
  let currentChart = null;
  let currentTheme = 'am4themes_animated';
  let detectedChartType = null;
  let cleanedData = false;

  // Initialisation
  document.addEventListener('DOMContentLoaded', () => {
    initEventListeners();
    updateColumnSelector();
  });

  function initEventListeners() {
    // Gestion des fichiers
    dropZone.addEventListener("click", () => fileInput.click());
    dropZone.addEventListener("dragover", e => { e.preventDefault(); dropZone.classList.add("dragover"); });
    dropZone.addEventListener("dragleave", () => dropZone.classList.remove("dragover"));
    dropZone.addEventListener("drop", e => {
      e.preventDefault();
      dropZone.classList.remove("dragover");
      handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener("change", e => handleFile(e.target.files[0]));

    // API Connection
    fetchApiData.addEventListener("click", fetchApiDataHandler);

    // Configuration graphique
    chartTypeSelect.addEventListener("change", updateChart);
    chartThemeSelect.addEventListener("change", updateTheme);
    enable3D.addEventListener("change", updateChart);
    enableAnimations.addEventListener("change", updateChart);

    // Export
    document.getElementById("exportPNG").addEventListener("click", exportPNG);
    document.getElementById("exportPDF").addEventListener("click", exportPDF);
    document.getElementById("exportCSV").addEventListener("click", exportCSV);
    document.getElementById("exportJSON").addEventListener("click", exportJSON);

    // Analyse IA
    analyzeTrends.addEventListener("click", detectTrends);
    findOutliers.addEventListener("click", findDataOutliers);
    predictValues.addEventListener("click", predictFutureValues);
    autoDetect.addEventListener("click", autoDetectBestVisualization);

    // Data Cleaning
    applyCleaning.addEventListener("click", applyDataCleaning);
  }

  // Gestion des fichiers
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
    } else if (ext === "xml") {
      reader.onload = e => parseXML(e.target.result);
      reader.readAsText(file);
    }
  }

  function parseJSON(jsonString) {
    try {
      const data = JSON.parse(jsonString);
      if (!Array.isArray(data)) throw new Error("JSON doit être un tableau de lignes.");

      currentFields = Object.keys(data[0]);
      currentData = data.filter(row => row[currentFields[0]]);
      originalData = [...currentData];

      updateColumnSelector();
      renderTable();
      checkDataQuality();
      updateChart();
    } catch (err) {
      showError("Erreur lors de l'analyse du JSON : " + err.message);
    }
  }

  function parseCSV(csv) {
    try {
      const result = Papa.parse(csv, { header: true, skipEmptyLines: true });

      if (result.errors.length > 0) {
        console.warn("CSV parsing warnings:", result.errors);
      }

      currentFields = result.meta.fields || [];
      currentData = result.data.filter(row => row && Object.values(row).some(val => val !== undefined && val !== ""));
      originalData = [...currentData];

      updateColumnSelector();
      renderTable();
      checkDataQuality();
      updateChart();
    } catch (err) {
      showError("Erreur lors de l'analyse du CSV : " + err.message);
    }
  }

  function parseXML(xmlString) {
    try {
      const parser = new DOMParser();
      const xmlDoc = parser.parseFromString(xmlString, "text/xml");
      const items = xmlDoc.getElementsByTagName("item") || xmlDoc.getElementsByTagName("row");

      if (items.length === 0) {
        throw new Error("Aucune donnée trouvée dans le XML");
      }

      currentFields = [];
      currentData = [];

      // Extract fields from first item
      const firstItem = items[0];
      for (let i = 0; i < firstItem.childNodes.length; i++) {
        const node = firstItem.childNodes[i];
        if (node.nodeType === 1) { // ELEMENT_NODE
          currentFields.push(node.nodeName);
        }
      }

      // Extract data
      for (let i = 0; i < items.length; i++) {
        const item = items[i];
        const row = {};

        for (let j = 0; j < item.childNodes.length; j++) {
          const node = item.childNodes[j];
          if (node.nodeType === 1) { // ELEMENT_NODE
            row[node.nodeName] = node.textContent;
          }
        }

        currentData.push(row);
      }

      originalData = [...currentData];
      updateColumnSelector();
      renderTable();
      checkDataQuality();
      updateChart();
    } catch (err) {
      showError("Erreur lors de l'analyse du XML : " + err.message);
    }
  }

  function fetchApiDataHandler() {
    const endpoint = apiEndpoint.value.trim();
    if (!endpoint) {
      showError("Veuillez entrer une URL d'API valide");
      return;
    }

    fetch(endpoint)
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
      })
      .then(data => {
        if (Array.isArray(data)) {
          currentData = data;
        } else if (typeof data === 'object' && data !== null) {
          // Try to find array in response
          const arrayData = findArrayInResponse(data);
          if (arrayData) {
            currentData = arrayData;
          } else {
            currentData = [data]; // Wrap single object in array
          }
        } else {
          throw new Error("Le format de réponse de l'API n'est pas supporté");
        }

        currentFields = Object.keys(currentData[0]);
        originalData = [...currentData];

        updateColumnSelector();
        renderTable();
        checkDataQuality();
        updateChart();
      })
      .catch(err => {
        showError("Erreur lors de la récupération des données API : " + err.message);
      });
  }

  function findArrayInResponse(obj) {
    // Check if obj is already an array
    if (Array.isArray(obj)) return obj;

    // Check object values for arrays
    for (const key in obj) {
      if (Array.isArray(obj[key])) {
        return obj[key];
      }
    }

    // Check nested objects
    for (const key in obj) {
      if (typeof obj[key] === 'object' && obj[key] !== null) {
        const nested = findArrayInResponse(obj[key]);
        if (nested) return nested;
      }
    }

    return null;
  }

  function updateColumnSelector() {
    if (!currentFields || currentFields.length === 0) {
      columnSelector.innerHTML = `
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i> Aucune donnée chargée ou format non reconnu
        </div>
        <button class="btn btn-sm btn-outline-primary w-100" id="showApiConnection">
          <i class="fas fa-plug"></i> Se connecter à une API
        </button>
      `;
      document.getElementById("showApiConnection").addEventListener("click", () => {
        apiConnection.style.display = 'block';
      });
      return;
    }

    let columnOptions = currentFields.map(field => `
      <option value="${field}">${field}</option>
    `).join("");

    columnSelector.innerHTML = `
      <div class="mb-3">
        <label class="form-label"><i class="fas fa-tag"></i> Colonne de catégories</label>
        <select id="categoryField" class="form-select">
          ${columnOptions}
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label"><i class="fas fa-chart-bar"></i> Colonnes de valeurs</label>
        <div class="scrollable-checkboxes" style="max-height: 200px; overflow-y: auto;">
          ${currentFields.map(field => `
            <div class="form-check">
              <input class="form-check-input series-checkbox" type="checkbox" value="${field}" id="series-${field}">
              <label class="form-check-label" for="series-${field}">${field}</label>
            </div>
          `).join("")}
        </div>
      </div>
      <div class="d-grid gap-2">
        <button class="btn btn-sm btn-warning" id="cleanDataBtn">
          <i class="fas fa-broom"></i> Nettoyer les données
        </button>
      </div>
    `;

    // Auto-select first field as category and next fields as series
    document.getElementById("categoryField").value = currentFields[0];
    const checkboxes = document.querySelectorAll('.series-checkbox');
    checkboxes.forEach((cb, index) => {
      if (index > 0 && index < 4) { // Select first 3 series by default
        cb.checked = true;
      }
    });

    // Add event listeners
    document.getElementById("categoryField").addEventListener("change", updateChart);
    document.querySelectorAll(".series-checkbox").forEach(cb => {
      cb.addEventListener("change", updateChart);
    });
    document.getElementById("cleanDataBtn").addEventListener("click", showDataCleaningModal);
  }

  function showDataCleaningModal() {
    // Populate column cleaner
    const columnCleaner = document.getElementById("columnCleaner");
    columnCleaner.innerHTML = currentFields.map(field => `
      <div class="form-check">
        <input class="form-check-input" type="checkbox" value="${field}" id="clean-${field}" checked>
        <label class="form-check-label" for="clean-${field}">${field}</label>
      </div>
    `).join("");

    cleaningModal.show();
  }

  function applyDataCleaning() {
    const selectedColumns = Array.from(document.querySelectorAll('#columnCleaner input:checked')).map(cb => cb.value);
    const strategy = document.getElementById("missingValuesStrategy").value;
    const normalize = document.getElementById("normalizeData").checked;
    const logTransform = document.getElementById("logTransform").checked;

    // Filter columns
    currentData = originalData.map(row => {
      const newRow = {};
      selectedColumns.forEach(col => {
        newRow[col] = row[col];
      });
      return newRow;
    });

    // Handle missing values
    currentData = currentData.filter(row => {
      return selectedColumns.every(col => row[col] !== undefined && row[col] !== null && row[col] !== "");
    });

    if (strategy !== 'remove') {
      selectedColumns.forEach(col => {
        if (isNumericColumn(col)) {
          const values = currentData.map(row => parseFloat(row[col])).filter(v => !isNaN(v));

          if (values.length === 0) return;

          let replacement;
          switch (strategy) {
            case 'mean':
              replacement = values.reduce((a, b) => a + b, 0) / values.length;
              break;
            case 'median':
              values.sort((a, b) => a - b);
              replacement = values[Math.floor(values.length / 2)];
              break;
            case 'zero':
              replacement = 0;
              break;
          }

          currentData.forEach(row => {
            if (row[col] === undefined || row[col] === null || row[col] === "" || isNaN(row[col])) {
              row[col] = replacement;
            }
          });
        }
      });
    }

    // Apply transformations
    if (normalize || logTransform) {
      selectedColumns.forEach(col => {
        if (isNumericColumn(col)) {
          const values = currentData.map(row => parseFloat(row[col]));
          const min = Math.min(...values);
          const max = Math.max(...values);

          currentData.forEach(row => {
            let val = parseFloat(row[col]);
            if (logTransform) {
              val = Math.log(val + 1); // Add 1 to avoid log(0)
            }
            if (normalize) {
              val = (val - min) / (max - min);
            }
            row[col] = val;
          });
        }
      });
    }

    currentFields = selectedColumns;
    cleanedData = true;
    cleaningModal.hide();
    updateColumnSelector();
    renderTable();
    updateChart();
  }

  function isNumericColumn(column) {
    if (!currentData.length) return false;

    const sampleSize = Math.min(10, currentData.length);
    let numericCount = 0;

    for (let i = 0; i < sampleSize; i++) {
      const value = currentData[i][column];
      if (value !== undefined && value !== null && value !== "" && !isNaN(value)) {
        numericCount++;
      }
    }

    return numericCount > sampleSize / 2;
  }

  function checkDataQuality() {
    if (!currentData.length) return;

    let issues = [];

    // Check for missing values
    currentFields.forEach(field => {
      const missing = currentData.filter(row => row[field] === undefined || row[field] === null || row[field] === "").length;
      if (missing > 0) {
        issues.push(`${missing} valeurs manquantes dans ${field}`);
      }
    });

    // Check for non-numeric values in numeric columns
    currentFields.forEach(field => {
      if (isNumericColumn(field)) {
        const nonNumeric = currentData.filter(row => {
          const val = row[field];
          return val !== undefined && val !== null && val !== "" && isNaN(val);
        }).length;

        if (nonNumeric > 0) {
          issues.push(`${nonNumeric} valeurs non-numériques dans ${field}`);
        }
      }
    });

    if (issues.length > 0) {
      showWarning("Problèmes détectés dans les données :<br>" + issues.join("<br>"));
    }
  }

  function renderTable() {
    if (!currentData.length || !currentFields.length) {
      dataTable.innerHTML = '<tr><td colspan="' + (currentFields.length || 1) + '">Aucune donnée à afficher</td></tr>';
      rowCount.textContent = '0 lignes';
      return;
    }

    const thead = `<thead><tr>${currentFields.map(h => `<th>${h}</th>`).join("")}</tr></thead>`;
    const tbody = `<tbody>${currentData.map(r => `<tr>${currentFields.map(h => `<td>${r[h] !== undefined && r[h] !== null ? r[h] : '<span class="text-muted">NULL</span>'}</td>`).join("")}</tr>`).join("")}</tbody>`;
    dataTable.innerHTML = thead + tbody;
    rowCount.textContent = currentData.length + ' ligne' + (currentData.length > 1 ? 's' : '');

    // Add hover effect
    dataTable.querySelectorAll('tr').forEach((row, i) => {
      if (i > 0) { // Skip header
        row.addEventListener('mouseover', () => {
          row.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseout', () => {
          row.style.backgroundColor = '';
        });
      }
    });
  }

  function updateChart() {
    if (!currentData.length || !currentFields.length) return;

    // Destroy existing chart
    if (currentChart) {
      currentChart.dispose();
    }

    // Get selections
    const categoryField = document.getElementById("categoryField").value;
    const selectedSeries = Array.from(document.querySelectorAll('.series-checkbox:checked')).map(cb => cb.value);

    if (selectedSeries.length === 0) {
      showWarning("Veuillez sélectionner au moins une série de données");
      return;
    }

    const chartType = chartTypeSelect.value;
    const use3D = enable3D.checked;
    const useAnimations = enableAnimations.checked;

    // Initialize chart
    currentChart = echarts.init(document.getElementById('chartdiv'));

    // Prepare data
    const seriesData = selectedSeries.map(series => {
      return {
        name: series,
        type: getSeriesType(chartType),
        data: currentData.map(row => {
          let value = row[series];
          // Try to convert to number if possible
          if (typeof value === 'string' && !isNaN(value)) {
            value = parseFloat(value);
          }
          return {
            name: row[categoryField],
            value: value
          };
        }),
        // Additional series-specific config
        ...getSeriesConfig(chartType, use3D)
      };
    });

    // Chart options
    const options = {
      tooltip: {
        trigger: 'item',
        formatter: params => {
          if (chartType === 'pie' || chartType === 'donut') {
            return `${params.name}<br/>${params.seriesName}: ${params.value}<br/>Pourcentage: ${params.percent}%`;
          }
          return `${params.seriesName}<br/>${params.name}: ${params.value}`;
        }
      },
      legend: {
        data: selectedSeries,
        type: 'scroll',
        orient: 'horizontal',
        bottom: 0
      },
      series: seriesData,
      animation: useAnimations,
      ...getChartSpecificOptions(chartType, use3D, categoryField)
    };

    // Apply theme
    switch (chartThemeSelect.value) {
      case 'am4themes_dark':
        options.backgroundColor = '#2c3e50';
        options.textStyle = { color: '#ecf0f1' };
        break;
      case 'am4themes_material':
        options.backgroundColor = '#fafafa';
        break;
      case 'am4themes_dataviz':
        options.backgroundColor = '#ffffff';
        break;
    }

    currentChart.setOption(options);

    // Handle window resize
    window.addEventListener('resize', function () {
      currentChart.resize();
    });
  }

  function getSeriesType(chartType) {
    switch (chartType) {
      case 'column': return 'bar';
      case 'bar': return 'bar';
      case 'line': return 'line';
      case 'pie': return 'pie';
      case 'donut': return 'pie';
      case 'radar': return 'radar';
      case 'xy': return 'scatter';
      case 'heatmap': return 'heatmap';
      case 'treemap': return 'treemap';
      case 'network': return 'graph';
      case 'sankey': return 'sankey';
      default: return 'bar';
    }
  }

  function getSeriesConfig(chartType, use3D) {
    const config = {};

    switch (chartType) {
      case 'pie':
      case 'donut':
        config.radius = chartType === 'donut' ? ['40%', '70%'] : '70%';
        config.emphasis = {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        };
        if (use3D) {
          config.type = 'pie3D';
          config.itemStyle = {
            borderColor: '#fff',
            borderWidth: 1
          };
        }
        break;

      case 'radar':
        config.areaStyle = {};
        break;

      case 'xy':
        config.symbolSize = 12;
        break;

      case 'heatmap':
        config.emphasis = {
          itemStyle: {
            shadowBlur: 10,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        };
        break;
    }

    return config;
  }

  function getChartSpecificOptions(chartType, use3D, categoryField) {
    const options = {};

    switch (chartType) {
      case 'column':
      case 'bar':
      case 'line':
        options.xAxis = {
          type: chartType === 'bar' ? 'value' : 'category',
          data: chartType === 'bar' ? undefined : currentData.map(row => row[categoryField])
        };
        options.yAxis = {
          type: chartType === 'bar' ? 'category' : 'value',
          data: chartType === 'bar' ? currentData.map(row => row[categoryField]) : undefined
        };
        break;

      case 'pie':
      case 'donut':
        if (use3D) {
          options.grid3D = {};
          options.viewControl = {
            autoRotate: true
          };
        }
        break;

      case 'radar':
        options.radar = {
          indicator: currentData.map(row => ({
            name: row[categoryField],
            max: Math.max(...currentData.map(r => parseFloat(r[selectedSeries[0]]) || 0))
          }))
        };
        break;

      case 'xy':
        options.xAxis = { type: 'value' };
        options.yAxis = { type: 'value' };
        break;

      case 'heatmap':
        options.xAxis = {
          type: 'category',
          data: currentData.map(row => row[categoryField]),
          splitArea: { show: true }
        };
        options.yAxis = {
          type: 'category',
          data: selectedSeries,
          splitArea: { show: true }
        };
        options.visualMap = {
          min: 0,
          max: Math.max(...currentData.flatMap(row =>
            selectedSeries.map(series => parseFloat(row[series]) || 0))
          ),
            calculable: true,
            orient: 'horizontal',
            left: 'center',
            bottom: '5%'
        };
        break;

      case 'treemap':
        options.series[0].data = currentData.map(row => ({
          name: row[categoryField],
          value: parseFloat(row[selectedSeries[0]]) || 0
        }));
        break;

      case 'network':
        // This is a simplified network graph - you'd need real relationship data
        options.series[0].data = currentData.map(row => ({
          name: row[categoryField],
          value: parseFloat(row[selectedSeries[0]]) || 0,
          symbolSize: 20 + (parseFloat(row[selectedSeries[0]]) || 0) / 10
        }));
        options.series[0].links = generateRandomLinks(currentData, categoryField);
        options.series[0].emphasis = {
          focus: 'adjacency',
          label: {
            position: 'right',
            show: true
          }
        };
        break;

      case 'sankey':
        options.series[0].data = currentData.map(row => ({
          name: row[categoryField]
        }));
        options.series[0].links = generateSankeyLinks(currentData, categoryField, selectedSeries[0]);
        break;
    }

    return options;
  }

  function generateRandomLinks(data, categoryField) {
    const links = [];
    const maxLinks = Math.min(10, data.length);

    for (let i = 0; i < maxLinks; i++) {
      const source = Math.floor(Math.random() * data.length);
      let target = Math.floor(Math.random() * data.length);

      // Ensure target is different from source
      while (target === source) {
        target = Math.floor(Math.random() * data.length);
      }

      links.push({
        source: data[source][categoryField],
        target: data[target][categoryField],
        value: Math.random() * 10
      });
    }

    return links;
  }

  function generateSankeyLinks(data, categoryField, valueField) {
    const links = [];

    // Simple implementation - connects each item to the next one
    for (let i = 0; i < data.length - 1; i++) {
      links.push({
        source: data[i][categoryField],
        target: data[i + 1][categoryField],
        value: parseFloat(data[i][valueField]) || 1
      });
    }

    return links;
  }

  function updateTheme() {
    updateChart(); // ECharts handles themes differently
  }

  // Analyse IA
  function detectTrends() {
    if (!currentData.length || !currentFields.length) return;

    const categoryField = document.getElementById("categoryField").value;
    const selectedSeries = Array.from(document.querySelectorAll('.series-checkbox:checked')).map(cb => cb.value);

    if (selectedSeries.length === 0) {
      showWarning("Veuillez sélectionner au moins une série de données");
      return;
    }

    let insights = '<ul>';

    selectedSeries.forEach(series => {
      if (isNumericColumn(series)) {
        const values = currentData.map(row => parseFloat(row[series]));

        // Simple trend detection
        const firstValue = values[0];
        const lastValue = values[values.length - 1];
        const change = ((lastValue - firstValue) / firstValue) * 100;

        let trend;
        if (change > 10) {
          trend = `<span class="ai-trend">forte augmentation (+${change.toFixed(1)}%)</span>`;
        } else if (change > 2) {
          trend = `<span class="ai-trend">légère augmentation (+${change.toFixed(1)}%)</span>`;
        } else if (change < -10) {
          trend = `<span class="ai-trend">forte diminution (${change.toFixed(1)}%)</span>`;
        } else if (change < -2) {
          trend = `<span class="ai-trend">légère diminution (${change.toFixed(1)}%)</span>`;
        } else {
          trend = "stable";
        }

        insights += `<li><strong>${series}</strong>: ${trend} sur la période analysée</li>`;
      }
    });

    insights += '</ul>';
    aiInsights.innerHTML = insights;
    analysisResults.style.display = 'block';
  }

  function findDataOutliers() {
    if (!currentData.length || !currentFields.length) return;

    const categoryField = document.getElementById("categoryField").value;
    const selectedSeries = Array.from(document.querySelectorAll('.series-checkbox:checked')).map(cb => cb.value);

    if (selectedSeries.length === 0) {
      showWarning("Veuillez sélectionner au moins une série de données");
      return;
    }

    let insights = '<ul>';
    let outlierCount = 0;

    selectedSeries.forEach(series => {
      if (isNumericColumn(series)) {
        const values = currentData.map(row => parseFloat(row[series]));
        const sorted = [...values].sort((a, b) => a - b);

        // Calculate quartiles
        const q1 = sorted[Math.floor(sorted.length / 4)];
        const q3 = sorted[Math.ceil(sorted.length * 3 / 4)];
        const iqr = q3 - q1;
        const lowerBound = q1 - 1.5 * iqr;
        const upperBound = q3 + 1.5 * iqr;

        // Find outliers
        const outliers = currentData.filter((row, i) => {
          const val = values[i];
          return val < lowerBound || val > upperBound;
        });

        if (outliers.length > 0) {
          insights += `<li><strong>${series}</strong>: ${outliers.length} anomalies détectées (valeurs en dehors de ${lowerBound.toFixed(2)} à ${upperBound.toFixed(2)})</li>`;
          outlierCount += outliers.length;

          // Highlight outlier rows in table
          outliers.forEach(outlier => {
            const index = currentData.indexOf(outlier);
            if (index !== -1) {
              const row = dataTable.querySelectorAll('tbody tr')[index];
              if (row) {
                row.classList.add('highlight-cell');
                setTimeout(() => {
                  row.classList.remove('highlight-cell');
                }, 1500);
              }
            }
          });
        } else {
          insights += `<li><strong>${series}</strong>: Aucune anomalie détectée</li>`;
        }
      }
    });

    insights += '</ul>';

    if (outlierCount > 0) {
      insights += `<div class="alert alert-warning mt-2">
        <i class="fas fa-exclamation-triangle"></i> ${outlierCount} anomalies ont été détectées et surlignées dans le tableau
      </div>`;
    }

    aiInsights.innerHTML = insights;
    analysisResults.style.display = 'block';
  }

  function predictFutureValues() {
    if (!currentData.length || !currentFields.length) return;

    const categoryField = document.getElementById("categoryField").value;
    const selectedSeries = Array.from(document.querySelectorAll('.series-checkbox:checked')).map(cb => cb.value);

    if (selectedSeries.length === 0) {
      showWarning("Veuillez sélectionner au moins une série de données");
      return;
    }

    let insights = '<ul>';

    selectedSeries.forEach(series => {
      if (isNumericColumn(series)) {
        const values = currentData.map(row => parseFloat(row[series]));

        // Simple linear regression for prediction
        const n = values.length;
        const xSum = n * (n - 1) / 2;
        const x2Sum = n * (n - 1) * (2 * n - 1) / 6;
        const ySum = values.reduce((a, b) => a + b, 0);
        const xySum = values.reduce((sum, y, x) => sum + x * y, 0);

        const slope = (n * xySum - xSum * ySum) / (n * x2Sum - xSum * xSum);
        const intercept = (ySum - slope * xSum) / n;

        // Predict next 3 values
        const predictions = [];
        for (let i = 1; i <= 3; i++) {
          predictions.push(intercept + slope * (n + i - 1));
        }

        insights += `<li><strong>${series}</strong>: 
          Prédictions <span class="ai-prediction">${predictions.map(p => p.toFixed(2)).join(', ')}</span>
          (basé sur une tendance ${slope > 0 ? 'positive' : 'négative'})
        </li>`;
      }
    });

    insights += '</ul>';
    aiInsights.innerHTML = insights;
    analysisResults.style.display = 'block';
  }

  function autoDetectBestVisualization() {
    if (!currentData.length || !currentFields.length) return;

    const categoryField = document.getElementById("categoryField").value;
    const selectedSeries = Array.from(document.querySelectorAll('.series-checkbox:checked')).map(cb => cb.value);

    if (selectedSeries.length === 0) {
      showWarning("Veuillez sélectionner au moins une série de données");
      return;
    }

    // Simple detection logic
    let recommendedChart = 'column';

    // If only one series is selected
    if (selectedSeries.length === 1) {
      const series = selectedSeries[0];

      // Check if values are percentages (between 0 and 1)
      const isPercentage = currentData.every(row => {
        const val = parseFloat(row[series]);
        return val >= 0 && val <= 1;
      });

      if (isPercentage) {
        recommendedChart = 'pie';
      }
      // Check if values are part of a whole
      else if (Math.abs(currentData.reduce((sum, row) => sum + parseFloat(row[series]), 0) - 100) < 5) {
        recommendedChart = 'donut';
      }
      // Check for time series data
      else if (isTimeField(categoryField)) {
        recommendedChart = 'line';
      }
    }
    // Multiple series
    else {
      // Check for time series data
      if (isTimeField(categoryField)) {
        recommendedChart = 'line';
      }
      // Check for correlation between series
      else if (selectedSeries.length === 2) {
        recommendedChart = 'xy';
      }
      // Many series with similar scale
      else if (selectedSeries.length > 3) {
        recommendedChart = 'radar';
      }
    }

    // Set the detected chart type
    chartTypeSelect.value = recommendedChart;
    updateChart();

    showSuccess(`Visualisation automatique détectée: ${document.querySelector(`#chartType option[value="${recommendedChart}"]`).textContent}`);
  }

  function isTimeField(field) {
    // Simple check for date/time fields
    const sample = currentData[0][field];
    return typeof sample === 'string' &&
      (sample.match(/\d{4}-\d{2}-\d{2}/) ||
        sample.match(/\d{2}\/\d{2}\/\d{4}/) ||
        sample.match(/\d{1,2}:\d{2}/));
  }

  // Export functions
  function exportPNG() {
    if (!currentChart) return;

    const img = new Image();
    img.src = currentChart.getDataURL({
      type: 'png',
      pixelRatio: 2,
      backgroundColor: '#fff'
    });

    const win = window.open('', '_blank');
    win.document.write('<img src="' + img.src + '" />');
    win.document.close();
  }

  function exportPDF() {
    if (!currentChart) return;

    // Simple implementation - would need jsPDF for proper PDF generation
    exportPNG(); // Fallback to PNG for this demo
  }

  function exportCSV() {
    if (!currentData.length) return;

    let csv = Papa.unparse({
      fields: currentFields,
      data: currentData
    });

    downloadFile(csv, 'data_export.csv', 'text/csv;charset=utf-8;');
  }

  function exportJSON() {
    if (!currentData.length) return;

    const json = JSON.stringify(currentData, null, 2);
    downloadFile(json, 'data_export.json', 'application/json');
  }

  function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // UI Helpers
  function showError(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
      <i class="fas fa-exclamation-circle"></i> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
  }

  function showWarning(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-warning alert-dismissible fade show';
    alert.innerHTML = `
      <i class="fas fa-exclamation-triangle"></i> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
  }

  function showSuccess(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.innerHTML = `
      <i class="fas fa-check-circle"></i> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
  }

</script>

<?php require_once '../includes/footer.php'; ?>