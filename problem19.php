<?php
function countDirectoryStats(string $dirPath): array {
    $stats = [
        "files" => 0,
        "subfolders" => 0,
        "total_bytes" => 0,
    ];

    if (!is_dir($dirPath)) {
        return $stats;
    }

    $entries = @scandir($dirPath);
    if ($entries === false) {
        return $stats;
    }

    foreach ($entries as $entry) {
        if ($entry === "." || $entry === "..") {
            continue;
        }
        $fullPath = $dirPath . DIRECTORY_SEPARATOR . $entry;
        if (is_link($fullPath)) {
            continue;
        }
        if (is_dir($fullPath)) {
            $stats["subfolders"]++;
            $nested = countDirectoryStats($fullPath);
            $stats["files"] += $nested["files"];
            $stats["subfolders"] += $nested["subfolders"];
            $stats["total_bytes"] += $nested["total_bytes"];
        } elseif (is_file($fullPath)) {
            $stats["files"]++;
            $size = @filesize($fullPath);
            if ($size !== false) {
                $stats["total_bytes"] += $size;
            }
        }
    }

    return $stats;
}

$targetDir = trim($_GET["dir"] ?? __DIR__);
$realTarget = realpath($targetDir);
$report = null;
$error = "";

if ($realTarget === false || !is_dir($realTarget)) {
    $error = "Invalid directory path.";
} else {
    $report = countDirectoryStats($realTarget);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recursive Directory File Counter (Problem 19)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-list {
            list-style: none;
            margin-top: 12px;
        }
        .report-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(51, 65, 85, 0.55);
        }
        .report-list li:last-child {
            border-bottom: none;
        }
        .path-code {
            word-break: break-all;
            color: #38bdf8;
        }
    </style>
</head>
<body>
    <h3>Recursive Directory File Counter (Problem 19)</h3>

    <form method="get">
        <label for="dir">Folder Path:</label>
        <input id="dir" type="text" name="dir" value="<?= htmlspecialchars($targetDir) ?>" required>
        <button type="submit">Scan Folder</button>
    </form>

    <?php if ($error !== ""): ?>
        <p style="color:red">❌ <?= htmlspecialchars($error) ?></p>
    <?php elseif ($report !== null): ?>
        <div class="container">
            <h4>Summary Report</h4>
            <p class="path-code"><?= htmlspecialchars($realTarget) ?></p>
            <ul class="report-list">
                <li>Total Files: <strong><?= (int)$report["files"] ?></strong></li>
                <li>Total Subfolders: <strong><?= (int)$report["subfolders"] ?></strong></li>
                <li>Total Size: <strong><?= number_format($report["total_bytes"]) ?> bytes</strong></li>
            </ul>
        </div>
    <?php endif; ?>
</body>
</html>