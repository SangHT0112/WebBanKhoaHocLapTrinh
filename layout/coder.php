<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>VSCode Code Practice</title>

  <!-- CodeMirror CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/edit/closebrackets.min.js"></script>

  <style>
    body {
      margin: 0;
      background: #1e1e1e;
      font-family: monospace;
      color: white;
    }
    header {
      padding: 12px;
      background: #252526;
      font-size: 18px;
      border-bottom: 1px solid #333;
    }
    #editor {
      height: 70vh;
      border-bottom: 1px solid #333;
    }
    #runBtn {
      width: 100%;
      padding: 15px;
      font-size: 18px;
      background: #0e639c;
      color: white;
      border: none;
      cursor: pointer;
    }
    #runBtn:hover {
      background: #0b4f7a;
    }
    #result {
      padding: 20px;
      height: 20vh;
      background: #1e1e1e;
      overflow-y: auto;
      color: #dcdcaa;
      white-space: pre-wrap;
    }
    .error { color: #f44747; }
  </style>
</head>
<body>

<header>VSCode-like Practice Editor</header>

<textarea id="editor"></textarea>
<button id="runBtn">Chạy Code</button>
<div id="result">Kết quả sẽ hiển thị ở đây...</div>

<script>
  const editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
    mode: "javascript",
    lineNumbers: true,
    theme: "default",
    autoCloseBrackets: true,
    tabSize: 2,
  });

  document.getElementById("runBtn").onclick = () => {
    const resultBox = document.getElementById("result");
    resultBox.innerHTML = "";

    try {
      const output = eval(editor.getValue());
      resultBox.textContent = output !== undefined ? output : "(Không có output)";
    } catch (err) {
      resultBox.innerHTML = `<span class='error'>Lỗi: ${err}</span>`;
    }
  };
</script>

</body>
</html>