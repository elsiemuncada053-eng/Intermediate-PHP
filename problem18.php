<?php
$books = [
    ["title" => "Clean Code", "author" => "Robert C. Martin", "year" => 2008, "category" => "Programming"],
    ["title" => "The Pragmatic Programmer", "author" => "Andrew Hunt", "year" => 1999, "category" => "Programming"],
    ["title" => "Atomic Habits", "author" => "James Clear", "year" => 2018, "category" => "Self-Help"],
    ["title" => "Sapiens", "author" => "Yuval Noah Harari", "year" => 2011, "category" => "History"],
    ["title" => "Designing Data-Intensive Applications", "author" => "Martin Kleppmann", "year" => 2017, "category" => "Programming"],
    ["title" => "Deep Work", "author" => "Cal Newport", "year" => 2016, "category" => "Self-Help"],
];

$categories = array_values(array_unique(array_column($books, "category")));

$query = trim($_GET["q"] ?? "");
$category = trim($_GET["category"] ?? "");
$minYear = filter_input(INPUT_GET, "min_year", FILTER_VALIDATE_INT);
$maxYear = filter_input(INPUT_GET, "max_year", FILTER_VALIDATE_INT);

$filtered = array_filter($books, function (array $book) use ($query, $category, $minYear, $maxYear): bool {
    if ($query !== "") {
        $haystack = mb_strtolower($book["title"] . " " . $book["author"]);
        if (!str_contains($haystack, mb_strtolower($query))) {
            return false;
        }
    }
    if ($category !== "" && $book["category"] !== $category) {
        return false;
    }
    if ($minYear !== false && $minYear !== null && $book["year"] < $minYear) {
        return false;
    }
    if ($maxYear !== false && $maxYear !== null && $book["year"] > $maxYear) {
        return false;
    }
    return true;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Search & Filter (Problem 18)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .year-row {
            display: flex;
            gap: 12px;
        }
        .year-row > div {
            flex: 1;
        }
        .form-footer {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
        }
        .reset-link {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
            white-space: nowrap;
        }
        .reset-link:hover {
            text-decoration: underline;
        }
        .count-bar {
            max-width: 580px;
            margin: 0 auto 10px auto;
            color: #94a3b8;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
    <h3>Book Search & Filter (Problem 18)</h3>

    <form method="get">
        <label for="q">Search (Title/Author):</label>
        <input id="q" type="text" name="q" value="<?= htmlspecialchars($query) ?>">

        <label for="category">Category:</label>
        <select id="category" name="category">
            <option value="">All</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? "selected" : "" ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="year-row">
            <div>
                <label for="min_year">Year From:</label>
                <input id="min_year" type="number" name="min_year" value="<?= ($minYear !== false && $minYear !== null) ? $minYear : "" ?>">
            </div>
            <div>
                <label for="max_year">To:</label>
                <input id="max_year" type="number" name="max_year" value="<?= ($maxYear !== false && $maxYear !== null) ? $maxYear : "" ?>">
            </div>
        </div>

        <div class="form-footer">
            <button type="submit">Filter</button>
            <a class="reset-link" href="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">Reset</a>
        </div>
    </form>

    <p class="count-bar">Showing <strong><?= count($filtered) ?></strong> book(s)</p>
    <table>
        <thead>
            <tr><th>Title</th><th>Author</th><th>Year</th><th>Category</th></tr>
        </thead>
        <tbody>
            <?php if (empty($filtered)): ?>
                <tr><td colspan="4">No matching books found.</td></tr>
            <?php else: ?>
                <?php foreach ($filtered as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b["title"]) ?></td>
                        <td><?= htmlspecialchars($b["author"]) ?></td>
                        <td><?= (int)$b["year"] ?></td>
                        <td><?= htmlspecialchars($b["category"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>