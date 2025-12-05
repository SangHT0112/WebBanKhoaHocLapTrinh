<?php
// Quick test to check path calculation
$baseDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
$rootDir = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$relPath = str_replace($rootDir, '', $baseDir);
$relPath = ltrim($relPath, '/');
$levels = substr_count($relPath, '/');
$cssPath = str_repeat('../', $levels) . 'search-modal.css';
$jsPath = str_repeat('../', $levels) . 'search-functions.js';

echo "<!-- DEBUG INFO -->\n";
echo "<!-- SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . " -->\n";
echo "<!-- DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . " -->\n";
echo "<!-- baseDir: " . $baseDir . " -->\n";
echo "<!-- rootDir: " . $rootDir . " -->\n";
echo "<!-- relPath: " . $relPath . " -->\n";
echo "<!-- levels: " . $levels . " -->\n";
echo "<!-- cssPath: " . $cssPath . " -->\n";
echo "<!-- jsPath: " . $jsPath . " -->\n";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Path Debug</title>
</head>
<body>
    <h1>Path Calculation Debug</h1>
    <pre>
SCRIPT_FILENAME: <?= $_SERVER['SCRIPT_FILENAME'] ?>
DOCUMENT_ROOT: <?= $_SERVER['DOCUMENT_ROOT'] ?>
baseDir: <?= $baseDir ?>
rootDir: <?= $rootDir ?>
relPath: <?= $relPath ?>
levels: <?= $levels ?>
cssPath: <?= $cssPath ?>
jsPath: <?= $jsPath ?>
    </pre>
    
    <h2>Actual Files:</h2>
    <ul>
        <li>CSS exists: <?= file_exists(dirname(__FILE__) . '/search-modal.css') ? 'YES' : 'NO' ?></li>
        <li>JS exists: <?= file_exists(dirname(__FILE__) . '/search-functions.js') ? 'YES' : 'NO' ?></li>
    </ul>
</body>
</html>
