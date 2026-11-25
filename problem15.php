<?php
$items = array_map(fn($i) => "Item #{$i} — Sample Record", range(1, 100));
$perPage = 10;
$totalItems = count($items);
$totalPages = max(1, (int)ceil($totalItems / $perPage));

$rawPage = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT);
$currentPage = ($rawPage === false || $rawPage === null || $rawPage < 1) ? 1 : min($totalPages, $rawPage);

$offset = ($currentPage - 1) * $perPage;
$pageItems = array_slice($items, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pagination Demo (Problem 15)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .pager-list {
            list-style: none;
            margin: 16px 0;
            padding: 0;
        }
        .pager-list li {
            padding: 8px 12px;
            border-bottom: 1px solid rgba(51, 65, 85, 0.55);
        }
        .pager-list li:last-child {
            border-bottom: none;
        }
        .pager-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
        }
        .pager-nav a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
        }
        .pager-nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h3>Pagination Demo (Problem 15)</h3>

    <div class="container">
        <p>Page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (<?= $totalItems ?> total items)</p>

        <ul class="pager-list">
            <?php foreach ($pageItems as $item): ?>
                <li><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>

        <div class="pager-nav">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>">← Previous</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>">Next →</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>