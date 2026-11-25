<?php
$cookieTtl = time() + 3600;
$logFile = __DIR__ . "/feedback_submissions.csv";

$step1 = isset($_COOKIE["feedback_step1"]) ? json_decode($_COOKIE["feedback_step1"], true) : null;
$step2 = isset($_COOKIE["feedback_step2"]) ? json_decode($_COOKIE["feedback_step2"], true) : null;
if (!is_array($step1)) $step1 = ["name" => "", "email" => ""];
if (!is_array($step2)) $step2 = ["rating" => "5", "comments" => ""];

$step = (int)($_GET["step"] ?? 1);
$error = "";
$submitted = isset($_GET["submitted"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fromStep = (int)($_POST["from_step"] ?? 1);

    if ($fromStep === 1) {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid name and email.";
            $step = 1;
        } else {
            $step1 = ["name" => $name, "email" => $email];
            setcookie("feedback_step1", json_encode($step1), $cookieTtl, "/");
            header("Location: " . $_SERVER["PHP_SELF"] . "?step=2");
            exit;
        }
    } elseif ($fromStep === 2) {
        $rating = $_POST["rating"] ?? "5";
        $comments = trim($_POST["comments"] ?? "");
        if (!in_array($rating, ["1", "2", "3", "4", "5"], true) || $comments === "") {
            $error = "Please select a rating (1–5) and enter feedback comments.";
            $step = 2;
        } else {
            $step2 = ["rating" => $rating, "comments" => $comments];
            setcookie("feedback_step2", json_encode($step2), $cookieTtl, "/");
            header("Location: " . $_SERVER["PHP_SELF"] . "?step=3");
            exit;
        }
    } elseif ($fromStep === 3 && isset($_POST["confirm_submit"])) {
        if ($step1["name"] === "" || $step2["comments"] === "") {
            $error = "Incomplete session/cookie data. Please start from Step 1.";
            $step = 1;
        } else {
            $isNew = !file_exists($logFile);
            $fp = fopen($logFile, "a");
            if ($fp && flock($fp, LOCK_EX)) {
                if ($isNew) {
                    fputcsv($fp, ["submitted_at", "name", "email", "rating", "comments"]);
                }
                fputcsv($fp, [date("Y-m-d H:i:s"), $step1["name"], $step1["email"], $step2["rating"], $step2["comments"]]);
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            setcookie("feedback_step1", "", time() - 3600, "/");
            setcookie("feedback_step2", "", time() - 3600, "/");
            header("Location: " . $_SERVER["PHP_SELF"] . "?step=1&submitted=1");
            exit;
        }
    }
}

$step = max(1, min(3, $step));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multi-Step Feedback Form (Problem 20)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .back-link {
            display: inline-block;
            margin-bottom: 14px;
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .review-list {
            list-style: none;
            margin: 14px 0 18px 0;
        }
        .review-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(51, 65, 85, 0.55);
        }
        .review-list li:last-child {
            border-bottom: none;
        }
        .success-msg {
            max-width: 580px;
            margin: 14px auto;
            padding: 12px 16px;
            background: rgba(34, 197, 94, 0.14);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.45);
            border-left: 4px solid #22c55e;
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h3>Multi-Step Feedback Form (Problem 20) — Step <?= $step ?> of 3</h3>

    <?php if ($submitted): ?>
        <p class="success-msg">✅ Thank you! Your feedback has been recorded.</p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p style="color:red">❌ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <form method="post">
            <input type="hidden" name="from_step" value="1">
            <label for="name">Name:</label>
            <input id="name" type="text" name="name" value="<?= htmlspecialchars($step1["name"]) ?>" required>

            <label for="email">Email:</label>
            <input id="email" type="email" name="email" value="<?= htmlspecialchars($step1["email"]) ?>" required>

            <button type="submit">Next: Feedback →</button>
        </form>
    <?php elseif ($step === 2): ?>
        <form method="post">
            <input type="hidden" name="from_step" value="2">
            <label for="rating">Rating (1–5):</label>
            <select id="rating" name="rating">
                <?php for ($r = 1; $r <= 5; $r++): ?>
                    <option value="<?= $r ?>" <?= (string)$step2["rating"] === (string)$r ? "selected" : "" ?>><?= $r ?></option>
                <?php endfor; ?>
            </select>

            <label for="comments">Comments:</label>
            <textarea id="comments" name="comments" rows="4" required><?= htmlspecialchars($step2["comments"]) ?></textarea>

            <a class="back-link" href="?step=1">← Back to Step 1</a>
            <button type="submit">Next: Review →</button>
        </form>
    <?php else: ?>
        <div class="container">
            <h4>Review Your Answers</h4>
            <ul class="review-list">
                <li><strong>Name:</strong> <?= htmlspecialchars($step1["name"]) ?></li>
                <li><strong>Email:</strong> <?= htmlspecialchars($step1["email"]) ?></li>
                <li><strong>Rating:</strong> <?= htmlspecialchars($step2["rating"]) ?> / 5</li>
                <li><strong>Comments:</strong> <?= htmlspecialchars($step2["comments"]) ?></li>
            </ul>
            <form method="post" style="margin:0; padding:0; background:none; border:none; box-shadow:none; backdrop-filter:none;">
                <input type="hidden" name="from_step" value="3">
                <a class="back-link" href="?step=2">← Edit Feedback</a>
                <button type="submit" name="confirm_submit" value="1">Submit Feedback</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>