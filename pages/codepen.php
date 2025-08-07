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
<link href="https://fonts.googleapis.com/css2?family=Fira+Code&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/material.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/eclipse.min.css">
<style>
    :root {
        --bg: #2a2734;
        --text: #ccc;
        --border: #ab9ff2;
        --accent: #61dafb;
        --input-bg: #1e1e1e;
        --preview-bg: #fff;
    }

    body.light {
        --bg: #f9f9f9;
        --text: #222;
        --border: #ccc;
        --accent: #ab9ff2;
        --input-bg: #fff;
        --preview-bg: #f0f0f0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Fira Code', monospace;
        background-color: var(--bg);
        color: var(--text);
        min-height: 100vh;
        height: auto;
        display: flex;
        flex-direction: column;
    }

    .header {
        background-color: var(--bg);
        color: var(--text);
        text-align: center;
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header h1 {
        font-size: 1.3rem;
    }

    .controls {
        display: flex;
        gap: 10px;
        margin-right: 10px;
    }

    .controls button {
        background: none;
        border: 1px solid var(--text);
        color: var(--text);
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.8rem;
        transition: background 0.3s;
    }

    .controls button:hover {
        background: var(--border);
    }


    /* editeur */
    .main-layout {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .editor-panel {
        background-color: var(--input-bg);
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .editor-block {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    label {
        color: var(--accent);
        font-size: 0.9rem;
    }

    /* Textareas style */
    textarea.editor {
        font-family: 'Fira Code', monospace;
        tab-size: 4;
        background-color: var(--bg);
        color: var(--text);
        border-radius: 5px;
        padding: 10px;
        font-family: 'Fira Code', monospace;
        font-size: 0.9rem;
        resize: vertical;
        min-height: 150px;
        line-height: 1.5;
        caret-color: var(--text);
    }

    textarea.editor:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 2px #007acc33;
    }

    /* Output panel */
    .preview-panel {
        flex: 1;
        border-top: 1px solid #333;
        background-color: #fff;
    }

    .preview-panel iframe {
        width: 100%;
        min-height: 100vh;
        border: none;
    }

    /* Desktop layout */
    @media (min-width: 600px) {
        .main-layout {
            flex-direction: row;
        }

        .editor-panel {
            width: 35%;
            height: 100%;
            border-right: 1px solid #333;
            overflow-y: auto;
        }

        .preview-panel {
            width: 65%;
            height: 100%;
            border-top: none;
        }
    }

    .CodeMirror {
        border-radius: 5px;
        font-family: 'Fira Code', monospace;
        font-size: 0.9rem;
        background-color: var(--bg);
        color: var(--text);
        caret-color: var(--text);
        min-height: 150px;
    }

    .cm-tag {
        color: #4aa3ff;
    }

    .cm-attribute {
        color: #ffe100;
    }

    .cm-string {
        color: #a6e22e;
    }

    .cm-bracket {
        color: #ff5e99;
    }

    .cm-qualifier {
        color: #ffe100;
    }
</style>


<!--V1--<div class="head">
    <h1>Live Code Editor</h1>
    <div class="controls">
        <button id="toggleMode">🌙 Mode</button>
        <button id="reset">♻️ Reset</button>
        <a href="../"><button>⬅️ Back</button></a>
    </div>
</div>
<div class="main-layout">
    <div class="editor-panel">
        <div class="editor-block">
            <label for="html">HTML</label>
            <textarea id="html" class="editor"
                placeholder="Écris ton HTML ici..."><h1>Hello World</h1><p>What' up ?</p></textarea>
        </div>
        <div class="editor-block">
            <label for="css">CSS</label>
            <textarea id="css" class="editor" placeholder="Dev by berru-g">h1 {
  color: #ab9ff2;
  text-align: center;
  margin-top: 40px;
}</textarea>
        </div>
    </div>
    <div class="preview-panel">
        <iframe id="preview"></iframe>
    </div>
</div>-->

<div class="header">
    <h1>Live Code Editor</h1>
    <div class="controls">
        <button id="themeToggle"><i class="fa-solid fa-palette"></i> Thème</button>
        <button id="reset"><i class="fa-solid fa-trash-arrow-up"></i> Reset</button>
        <button id="save"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        <a href="https://github.com/berru-g/berru-g/blob/main/codepen/"><button>opensrc</button></a>
    </div>
</div>

<div class="main-layout">
    <div class="editor-panel">
        <div class="editor-block">
            <label for="htmlEditor">HTML</label>
            <textarea id="htmlEditor" placeholder="Dev by berru-g"><h1>Live code editor</h1></textarea>
        </div>
        <div class="editor-block">
            <label for="cssEditor">CSS</label>
            <textarea id="cssEditor">h1, p {
  color: #ab9ff2;
  text-align: center;
  margin-top: 40px;
}</textarea>
        </div>
    </div>
    <div class="preview-panel">
        <iframe id="preview"></iframe>
    </div>
</div>

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/matchbrackets.min.js"></script>

<script>
    const htmlEditor = CodeMirror.fromTextArea(document.getElementById("htmlEditor"), {
        mode: "htmlmixed",
        theme: "material",
        lineNumbers: true,
        tabSize: 2,
        autoCloseBrackets: true,
        matchBrackets: true
    });

    const cssEditor = CodeMirror.fromTextArea(document.getElementById("cssEditor"), {
        mode: "css",
        theme: "material",
        lineNumbers: true,
        tabSize: 2,
        autoCloseBrackets: true,
        matchBrackets: true
    });

    const iframe = document.getElementById("preview");

    function updatePreview() {
        const html = htmlEditor.getValue();
        const css = cssEditor.getValue();
        const content = `
        <html>
          <head><style>${css}</style></head>
          <body>${html}</body>
        </html>
      `;
        const preview = iframe.contentDocument || iframe.contentWindow.document;
        preview.open();
        preview.write(content);
        preview.close();
    }

    htmlEditor.on("change", updatePreview);
    cssEditor.on("change", updatePreview);

    // LocalStorage save/load
    if (localStorage.getItem("htmlCode")) htmlEditor.setValue(localStorage.getItem("htmlCode"));
    if (localStorage.getItem("cssCode")) cssEditor.setValue(localStorage.getItem("cssCode"));

    htmlEditor.on("change", () => localStorage.setItem("htmlCode", htmlEditor.getValue()));
    cssEditor.on("change", () => localStorage.setItem("cssCode", cssEditor.getValue()));

    document.getElementById("reset").addEventListener("click", () => {
        localStorage.clear();
        htmlEditor.setValue("");
        cssEditor.setValue("");
        updatePreview();
    });

    // Theme switch
    let isDark = true;
    document.getElementById("themeToggle").addEventListener("click", () => {
        isDark = !isDark;
        const theme = isDark ? "material" : "eclipse";
        htmlEditor.setOption("theme", theme);
        cssEditor.setOption("theme", theme);
        document.body.classList.toggle("light");
    });

    updatePreview();
</script>

<?php require_once '../includes/footer.php'; ?>