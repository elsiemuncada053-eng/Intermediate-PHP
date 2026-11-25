<?php
function checkPasswordStrength(string $password): string {
    $hasLength = mb_strlen($password) >= 8;
    $hasLetters = (bool)preg_match("/[a-zA-Z]/", $password);
    $hasNumbers = (bool)preg_match("/\d/", $password);
    $hasSpecial = (bool)preg_match("/[^a-zA-Z0-9]/", $password);

    if ($hasLength && $hasLetters && $hasNumbers && $hasSpecial) {
        return "Strong";
    }
    if ($hasLength && $hasLetters && $hasNumbers) {
        return "Medium";
    }
    return "Weak";
}

function generateStrongPassword(int $length = 12): string {
    $length = max(8, min(64, $length));
    $letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $digits  = "0123456789";
    $special = "!@#$%^&*()-_=+";
    $all     = $letters . $digits . $special;

    $chars = [
        $letters[random_int(0, strlen($letters) - 1)],
        $digits[random_int(0, strlen($digits) - 1)],
        $special[random_int(0, strlen($special) - 1)],
    ];

    for ($i = count($chars); $i < $length; $i++) {
        $chars[] = $all[random_int(0, strlen($all) - 1)];
    }

    for ($i = count($chars) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
    }

    return implode("", $chars);
}

$checkedPw = "";
$strength = "";
$genLength = 12;
$generatedPw = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["check_pw"])) {
        $checkedPw = $_POST["password"] ?? "";
        $strength = checkPasswordStrength($checkedPw);
    }
    if (isset($_POST["gen_pw"])) {
        $genLength = (int)($_POST["length"] ?? 12);
        $generatedPw = generateStrongPassword($genLength);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Strength Checker & Generator (Problem 14)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h3>Password Strength Checker & Generator (Problem 14)</h3>

    <h4>Part 1: Check Password</h4>
    <form method="post">
        <input type="text" name="password" value="<?= htmlspecialchars($checkedPw) ?>" placeholder="Enter password">
        <button type="submit" name="check_pw" value="1">Check Strength</button>
    </form>
    <?php if ($strength !== ""): ?>
        <p style="text-align:center;">Strength Rating: <strong><?= htmlspecialchars($strength) ?></strong></p>
    <?php endif; ?>

    <h4>Part 2: Generate Strong Password</h4>
    <form method="post">
        <label for="length">Length (8–64):</label>
        <input id="length" type="number" name="length" min="8" max="64" value="<?= (int)$genLength ?>">
        <button type="submit" name="gen_pw" value="1">Generate</button>
    </form>
    <?php if ($generatedPw !== ""): ?>
        <p style="text-align:center;">Generated Password: <code><?= htmlspecialchars($generatedPw) ?></code> (Rating: <strong><?= checkPasswordStrength($generatedPw) ?></strong>)</p>
    <?php endif; ?>
</body>
</html>