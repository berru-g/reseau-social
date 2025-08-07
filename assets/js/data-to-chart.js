const chartContainer = document.getElementById("myChart");
const columnSelector = document.getElementById("columnSelector");
const chartTypeSelect = document.getElementById("chartType");
const dropZone = document.getElementById("dropZone");
const fileInput = document.getElementById("dataUpload");
let chart;
alert("yo je lie le js");
dropZone.addEventListener("click", () => fileInput.click());
dropZone.addEventListener("dragover", e => { e.preventDefault(); dropZone.classList.add("dragover"); });
dropZone.addEventListener("dragleave", () => dropZone.classList.remove("dragover"));
dropZone.addEventListener("drop", e => {
  e.preventDefault();
  dropZone.classList.remove("dragover");
  handleFile(e.dataTransfer.files[0]);
});
fileInput.addEventListener("change", e => handleFile(e.target.files[0]));

function handleFile(file) {
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
  }
}

function parseCSV(csv) {
  const data = Papa.parse(csv, { header: true });
  const headers = data.meta.fields;
  const rows = data.data;
  columnSelector.innerHTML = headers.map(h => `<label class='me-2'><input type='checkbox' value='${h}' checked> ${h}</label>`).join("");
  renderChart(headers[0], headers[1], rows);
  columnSelector.addEventListener("change", () => {
    const selected = [...columnSelector.querySelectorAll("input:checked")].map(el => el.value);
    if (selected.length >= 2) renderChart(selected[0], selected[1], rows);
  });
}

function renderChart(labelKey, valueKey, rows) {
  const labels = rows.map(r => r[labelKey]);
  const values = rows.map(r => parseFloat(r[valueKey]));
  const type = chartTypeSelect.value;
  if (chart) chart.destroy();
  chart = new Chart(chartContainer, {
    type,
    data: {
      labels,
      datasets: [{
        label: `${valueKey} par ${labelKey}`,
        data: values,
        backgroundColor: "rgba(54, 162, 235, 0.5)",
        borderColor: "rgba(54, 162, 235, 1)",
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: "top" }, title: { display: true, text: "Visualisation des données" } }
    }
  });
}

document.getElementById("exportPNG").addEventListener("click", () => {
  const url = chart.toBase64Image();
  const a = document.createElement("a");
  a.href = url;
  a.download = "chart.png";
  a.click();
});

document.getElementById("exportPDF").addEventListener("click", () => {
  html2canvas(chartContainer).then(canvas => {
    const imgData = canvas.toDataURL("image/png");
    const pdf = new jspdf.jsPDF();
    pdf.addImage(imgData, "PNG", 10, 10, 180, 100);
    pdf.save("chart.pdf");
  });
});
