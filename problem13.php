<?php
$csvFile = __DIR__ . "/products.csv";
$error = "";
$success = "";

if (!file_exists($csvFile)) {
    $fp = fopen($csvFile, "w");
    fputcsv($fp, ["product_id", "name", "price", "stock"]);
    fputcsv($fp, ["P101", "USB Keyboard", "19.99", "25"]);
    fputcsv($fp, ["P102", "Optical Mouse", "14.50", "40"]);
    fclose($fp);
}

function loadProducts(string $path): array {
    $rows = [];
    if (!file_exists($path)) return $rows;
    $fp = fopen($path, "r");
    if (!$fp) return $rows;
    $header = fgetcsv($fp);
    while (($data = fgetcsv($fp)) !== false) {
        if (count($data) < 4) continue;
        $rows[] = [
            "product_id" => trim($data[0]),
            "name"       => trim($data[1]),
            "price"      => (float)$data[2],
            "stock"      => (int)$data[3],
        ];
    }
    fclose($fp);
    return $rows;
}

function saveProducts(string $path, array $rows): bool {
    $fp = fopen($path, "c");
    if (!$fp || !flock($fp, LOCK_EX)) return false;
    ftruncate($fp, 0);
    rewind($fp);
    fputcsv($fp, ["product_id", "name", "price", "stock"]);
    foreach ($rows as $r) {
        fputcsv($fp, [$r["product_id"], $r["name"], number_format($r["price"], 2, ".", ""), $r["stock"]]);
    }
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

$products = loadProducts($csvFile);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $id = trim($_POST["product_id"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $price = filter_var($_POST["price"] ?? "", FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST["stock"] ?? "", FILTER_VALIDATE_INT);

        if ($id === "" || $name === "" || $price === false || $price < 0 || $stock === false || $stock < 0) {
            $error = "Invalid product data. Provide ID, name, non-negative price, and non-negative stock.";
        } elseif (array_filter($products, fn($p) => strcasecmp($p["product_id"], $id) === 0)) {
            $error = "Product ID already exists.";
        } else {
            $products[] = ["product_id" => $id, "name" => $name, "price" => (float)$price, "stock" => (int)$stock];
            $success = saveProducts($csvFile, $products) ? "Product added." : "Failed to save CSV.";
        }
    } elseif ($action === "update_stock") {
        $id = trim($_POST["product_id"] ?? "");
        $stock = filter_var($_POST["stock"] ?? "", FILTER_VALIDATE_INT);
        $found = false;

        if ($stock === false || $stock < 0) {
            $error = "Stock must be a whole number 0 or higher.";
        } else {
            foreach ($products as &$p) {
                if ($p["product_id"] === $id) {
                    $p["stock"] = (int)$stock;
                    $found = true;
                    break;
                }
            }
            unset($p);
            if (!$found) {
                $error = "Product not found.";
            } else {
                $success = saveProducts($csvFile, $products) ? "Stock updated." : "Failed to save CSV.";
            }
        }
    } elseif ($action === "delete") {
        $id = trim($_POST["product_id"] ?? "");
        $before = count($products);
        $products = array_values(array_filter($products, fn($p) => $p["product_id"] !== $id));
        if (count($products) === $before) {
            $error = "Product not found.";
        } else {
            $success = saveProducts($csvFile, $products) ? "Product deleted." : "Failed to save CSV.";
        }
    }

    $products = loadProducts($csvFile);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSV Product Inventory Manager (Problem 13)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .inventory-table {
            max-width: 820px;
        }
        .inventory-table form {
            max-width: none;
            margin: 0;
            padding: 0;
            background: none;
            border: none;
            box-shadow: none;
            backdrop-filter: none;
        }
        .inline-update {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .inline-update input[type="number"] {
            width: 76px;
            margin-bottom: 0;
            padding: 7px 8px;
        }
        .inventory-table button {
            width: auto;
            padding: 7px 12px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <h3>CSV Product Inventory Manager (Problem 13)</h3>

    <?php if ($success !== ""): ?><p style="color:green">✅ <?= htmlspecialchars($success) ?></p><?php endif; ?>
    <?php if ($error !== ""): ?><p style="color:red">❌ <?= htmlspecialchars($error) ?></p><?php endif; ?>

    <h4>Add New Product</h4>
    <form method="post">
        <input type="hidden" name="action" value="add">
        ID: <input type="text" name="product_id" required>
        Name: <input type="text" name="name" required>
        Price: <input type="number" name="price" step="0.01" min="0" required>
        Stock: <input type="number" name="stock" min="0" required>
        <button type="submit">Add</button>
    </form>

    <h4>Inventory</h4>
    <table class="inventory-table">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Update Stock</th><th>Delete</th></tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p["product_id"]) ?></td>
                <td><?= htmlspecialchars($p["name"]) ?></td>
                <td><?= number_format($p["price"], 2) ?></td>
                <td><?= (int)$p["stock"] ?></td>
                <td>
                    <form method="post" class="inline-update">
                        <input type="hidden" name="action" value="update_stock">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($p["product_id"]) ?>">
                        <input type="number" name="stock" min="0" value="<?= (int)$p["stock"] ?>" required>
                        <button type="submit">Save</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($p["product_id"]) ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>