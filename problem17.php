<?php
$thirtyDays = time() + (60 * 60 * 24 * 30);
$defaultLang = "en";
$defaultTheme = "light";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "save";
    if ($action === "reset") {
        setcookie("pref_lang", "", time() - 3600, "/");
        setcookie("pref_theme", "", time() - 3600, "/");
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    } else {
        $lang = in_array($_POST["lang"] ?? "", ["en", "fil"], true) ? $_POST["lang"] : $defaultLang;
        $theme = in_array($_POST["theme"] ?? "", ["light", "dark"], true) ? $_POST["theme"] : $defaultTheme;
        setcookie("pref_lang", $lang, $thirtyDays, "/");
        setcookie("pref_theme", $theme, $thirtyDays, "/");
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}

$lang = in_array($_COOKIE["pref_lang"] ?? "", ["en", "fil"], true) ? $_COOKIE["pref_lang"] : $defaultLang;
$theme = in_array($_COOKIE["pref_theme"] ?? "", ["light", "dark"], true) ? $_COOKIE["pref_theme"] : $defaultTheme;

$heading = $lang === "fil" ? "Mga Kagustuhan sa Website (30 Araw)" : "Website Preferences (30 Days)";
$greeting = $lang === "fil"
    ? "Kumusta! Awtomatikong inilalapat ang iyong wika at tema."
    : "Hello! Your language and theme preferences are applied automatically.";
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title>Website Preferences (30 Days) - Problem 17</title>
    <link rel="stylesheet" href="style.css">
    <?php if ($theme === "light"): ?>
    <style>
        body {
            background: #f1f5f9 !important;
            color: #0f172a !important;
        }
        h1, h2, h3, h4 {
            color: #0f172a !important;
            text-shadow: none !important;
        }
        form, .container {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08) !important;
            backdrop-filter: none !important;
        }
        label {
            color: #334155 !important;
        }
        input, select, textarea {
            background: #f8fafc !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <h3><?= htmlspecialchars($heading) ?></h3>

    <div class="container" style="text-align:center;">
        <p><?= htmlspecialchars($greeting) ?></p>
        <p style="margin-top:8px;">Active: <strong><?= $lang === "fil" ? "Filipino" : "English" ?></strong> | <strong><?= ucfirst($theme) ?></strong></p>
    </div>

    <form method="post">
        <input type="hidden" name="action" value="save">
        <label for="lang">Language:</label>
        <select id="lang" name="lang">
            <option value="en" <?= $lang === "en" ? "selected" : "" ?>>English</option>
            <option value="fil" <?= $lang === "fil" ? "selected" : "" ?>>Filipino</option>
        </select>

        <label for="theme">Theme:</label>
        <select id="theme" name="theme">
            <option value="light" <?= $theme === "light" ? "selected" : "" ?>>Light</option>
            <option value="dark" <?= $theme === "dark" ? "selected" : "" ?>>Dark</option>
        </select>

        <button type="submit">Save Preferences</button>
    </form>

    <form method="post">
        <input type="hidden" name="action" value="reset">
        <button type="submit">Reset to Defaults</button>
    </form>
</body>
</html>