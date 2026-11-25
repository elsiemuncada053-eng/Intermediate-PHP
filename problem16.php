<?php
function generateGrid(int $size = 5): array {
    $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $grid = [];
    for ($r = 0; $r < $size; $r++) {
        $row = [];
        for ($c = 0; $c < $size; $c++) {
            $row[] = $letters[random_int(0, strlen($letters) - 1)];
        }
        $grid[] = $row;
    }
    return $grid;
}

function findWordInGrid(array $grid, string $word): ?array {
    $word = strtoupper(preg_replace("/[^A-Za-z]/", "", $word));
    $len = strlen($word);
    $rows = count($grid);
    $cols = $rows > 0 ? count($grid[0]) : 0;

    if ($len === 0 || $rows === 0 || $cols === 0) {
        return null;
    }

    $directions = [
        "Horizontal (Left-to-Right)" => [0, 1],
        "Horizontal (Right-to-Left)" => [0, -1],
        "Vertical (Top-to-Bottom)"   => [1, 0],
        "Vertical (Bottom-to-Top)"   => [-1, 0],
    ];

    for ($r = 0; $r < $rows; $r++) {
        for ($c = 0; $c < $cols; $c++) {
            foreach ($directions as $label => [$dr, $dc]) {
                $endR = $r + $dr * ($len - 1);
                $endC = $c + $dc * ($len - 1);
                if ($endR < 0 || $endR >= $rows || $endC < 0 || $endC >= $cols) {
                    continue;
                }

                $matched = true;
                for ($k = 0; $k < $len; $k++) {
                    if ($grid[$r + $dr * $k][$c + $dc * $k] !== $word[$k]) {
                        $matched = false;
                        break;
                    }
                }

                if ($matched) {
                    return [
                        "row" => $r + 1,
                        "col" => $c + 1,
                        "direction" => $label,
                        "word" => $word,
                    ];
                }
            }
        }
    }

    return null;
}

session_start();
if (!isset($_SESSION["grid5x5"]) || isset($_POST["new_grid"])) {
    $_SESSION["grid5x5"] = generateGrid(5);
}

$grid = $_SESSION["grid5x5"];
$target = trim($_POST["word"] ?? "");
$match = null;
$searched = false;
$emptySearch = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["search_word"])) {
    $searched = true;
    if ($target === "") {
        $emptySearch = true;
    } else {
        $match = findWordInGrid($grid, $target);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>5×5 Word Search Algorithm (Problem 16)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .grid-table {
            font-family: monospace;
            font-size: 18px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
        }
        .result-msg {
            max-width: 580px;
            margin: 14px auto;
            padding: 12px 16px;
            border-radius: 8px;
            text-align: center;
        }
        .result-found {
            background: rgba(34, 197, 94, 0.14);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.45);
            border-left: 4px solid #22c55e;
        }
    </style>
</head>
<body>
    <h3>5×5 Word Search Algorithm (Problem 16)</h3>

    <table class="grid-table">
        <?php foreach ($grid as $row): ?>
            <tr>
                <?php foreach ($row as $cell): ?>
                    <td><?= htmlspecialchars($cell) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <form method="post">
        <label for="word">Target Word:</label>
        <input id="word" type="text" name="word" value="<?= htmlspecialchars($target) ?>" placeholder="e.g. CAT">
        <div class="form-actions">
            <button type="submit" name="search_word" value="1">Search Word</button>
            <button type="submit" name="new_grid" value="1">New 5×5 Grid</button>
        </div>
    </form>

    <?php if ($searched): ?>
        <?php if ($emptySearch): ?>
            <p class="warning">⚠️ Please enter a word to search.</p>
        <?php elseif ($match): ?>
            <p class="result-msg result-found">✅ Found <strong><?= htmlspecialchars($match["word"]) ?></strong> starting at Row <?= $match["row"] ?>, Col <?= $match["col"] ?> — <strong><?= htmlspecialchars($match["direction"]) ?></strong>.</p>
        <?php else: ?>
            <p class="error">❌ Word not found horizontally or vertically.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>