<?php
// 設定根目錄
$root = __DIR__;
$current = isset($_GET['dir']) ? realpath($root . '/' . $_GET['dir']) : $root;
if (strpos($current, $root) !== 0) $current = $root;

// 檔案路徑
$filePath = '';
$content = '';
$selectedFile = isset($_GET['file']) ? $_GET['file'] : '';
if ($selectedFile) {
    // 將 UTF-8 的檔名轉成 Windows 檔案系統編碼 (CP950)
    $selectedFileFS = @iconv("UTF-8", "CP950//IGNORE", $selectedFile);
    $file = realpath($current . '/' . $selectedFileFS);
    if ($file && strpos($file, $root) === 0 && is_file($file)) {
        $filePath = $file;
        $raw = file_get_contents($file);
        // 判斷檔案編碼，必要時轉成 UTF-8
        $encoding = mb_detect_encoding($raw, ['UTF-8','CP950','BIG5','GBK'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $raw = iconv($encoding, 'UTF-8//IGNORE', $raw);
        }
        $content = htmlspecialchars($raw);
    }
}

// 目錄清單
$items = scandir($current);
$dirs = [];
$files = [];
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $path = $current . '/' . $item;
    if (is_dir($path)) {
        $dirs[] = $item;
    } else {
        $files[] = $item;
    }
}
natcasesort($dirs);
natcasesort($files);

// 相對 URL
function relativeUrl($root, $filePath) {
    $webRoot = str_replace('\\', '/', realpath($root));
    $webFile = str_replace('\\', '/', $filePath);
    $relative = str_replace($webRoot, '', $webFile);
    return dirname($_SERVER['SCRIPT_NAME']) . $relative;
}

// 中文檔名安全輸出
function safeName($name) {
    $converted = @iconv("CP950", "UTF-8//IGNORE", $name);
    if ($converted === false || $converted === '') {
        $converted = $name;
    }
    return htmlspecialchars($converted);
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>檔案瀏覽器</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { margin:0; overflow:hidden; display:flex; }
    #sidebar { width:250px; min-width:150px; max-width:600px; height:100vh; overflow-y:auto; background:#f8f9fa; border-right:1px solid #ddd; }
    #resizer { width:5px; cursor:col-resize; background:#ccc; }
    #content { flex:1; height:100vh; overflow-y:auto; }
    pre { white-space: pre-wrap; word-wrap: break-word; }
    .active-file { font-weight: bold; color: #0d6efd; }
  </style>
</head>
<body>
<div id="sidebar">
  <h5 class="p-2">目錄瀏覽</h5>
  <ul class="list-unstyled ps-3">
    <?php
    if ($current !== $root) {
        $parent = dirname($current);
        echo '<li><i class="fa fa-level-up-alt"></i> <a href="?dir=' . urlencode(str_replace($root, '', $parent)) . '">.. 返回上層</a></li>';
    }
    foreach ($dirs as $item) {
        $path = $current . '/' . $item;
        echo '<li><i class="fa fa-folder"></i> <a href="?dir=' . urlencode(str_replace($root, '', $path)) . '">' . safeName($item) . '</a></li>';
    }
    foreach ($files as $item) {
        $isActive = ($item === $selectedFile) ? 'active-file' : '';
        //echo '<li><i class="fa fa-file"></i> <a class="' . $isActive . '" href="?dir=' . urlencode(str_replace($root, '', $current)) . '&file=' . urlencode($item) . '">' . safeName($item) . '</a></li>';
        echo '<li><i class="fa fa-file"></i> <a class="' . $isActive . '" href="?dir=' . urlencode(str_replace($root, '', $current)) . '&file=' . urlencode(safeName($item)) . '">' . safeName($item) . '</a></li>';
    }
    ?>
  </ul>
</div>
<div id="resizer"></div>
<div id="content">
  <h5 class="p-2">檔案內容</h5>
  <?php if ($filePath): ?>
    <ul class="nav nav-tabs" id="fileTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="source-tab" data-bs-toggle="tab" data-bs-target="#source" type="button" role="tab">原始碼</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="run-tab" data-bs-toggle="tab" data-bs-target="#run" type="button" role="tab">執行結果</button>
      </li>
    </ul>
    <div class="tab-content border border-top-0 p-3 bg-white">
      <div class="tab-pane fade show active" id="source" role="tabpanel">
        <pre><?php echo $content; ?></pre>
      </div>
      <div class="tab-pane fade" id="run" role="tabpanel">
        <iframe src="<?php echo htmlspecialchars(relativeUrl($root, $filePath)); ?>" style="width:100%;height:70vh;border:none;"></iframe>
      </div>
    </div>
  <?php else: ?>
    <p class="p-3 text-muted">請選擇檔案以顯示內容。</p>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar 拖曳調整寬度 + 記憶功能
const sidebar = document.getElementById('sidebar');
const resizer = document.getElementById('resizer');
let isResizing = false;

// 載入時套用上次寬度
const savedWidth = localStorage.getItem('sidebarWidth');
if (savedWidth) {
  sidebar.style.width = savedWidth + 'px';
}

resizer.addEventListener('mousedown', function(e) {
  isResizing = true;
  document.body.style.cursor = 'col-resize';
});

document.addEventListener('mousemove', function(e) {
  if (!isResizing) return;
  let newWidth = e.clientX;
  if (newWidth < 150) newWidth = 150;
  if (newWidth > 600) newWidth = 600;
  sidebar.style.width = newWidth + 'px';
});

document.addEventListener('mouseup', function(e) {
  if (isResizing) {
    localStorage.setItem('sidebarWidth', sidebar.offsetWidth);
  }
  isResizing = false;
  document.body.style.cursor = 'default';
});
</script>
</body>
</html>
