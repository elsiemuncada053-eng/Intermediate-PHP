<?php
$files = glob(__DIR__ . "/problem*.php");
natsort($files);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>/website — PHP Exercises</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="hub-wrap">
        <h3>PHP Exercises Hub</h3>
        <p class="subtitle">Localhost /website — Select a script to run</p>

        <div class="grid">
            <?php foreach ($files as $path): ?>
                <?php
                    $name = basename($path);
                    $size = round(filesize($path) / 1024, 1);
                ?>
                <a class="card-link" href="<?= htmlspecialchars($name) ?>">
                    <span class="file-name"><?= htmlspecialchars($name) ?></span>
                    <span class="meta"><?= $size ?> KB</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

