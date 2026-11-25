<?php
$errors = [];
$success = "";
$name = "";
$email = "";
$message = "";

$uploadDir = __DIR__ . "/uploads";
$logFile = __DIR__ . "/contact_log.csv";
$allowedMimes = [
    "application/pdf" => "pdf",
    "image/jpeg"      => "jpg",
    "image/png"       => "png",
    "image/webp"      => "webp",
];
$maxBytes = 2 * 1024 * 1024; // 2 MB

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $savedFilename = "";

    if ($name === "" || mb_strlen($name) < 2) {
        $errors[] = "Please enter your name (at least 2 characters).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if ($message === "") {
        $errors[] = "Please enter a message.";
    }

    $hasFile = isset($_FILES["attachment"]) && ($_FILES["attachment"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if ($hasFile) {
        $errCode = $_FILES["attachment"]["error"];
        $size = (int)$_FILES["attachment"]["size"];
        $tmpPath = $_FILES["attachment"]["tmp_name"];

        if ($errCode !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed (error code {$errCode}).";
        } elseif ($size <= 0 || $size > $maxBytes) {
            $errors[] = "Attachment must be larger than 0 bytes and at most 2 MB.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo ? finfo_file($finfo, $tmpPath) : false;
            if ($finfo) {
                finfo_close($finfo);
            }

            if (!$detectedMime || !isset($allowedMimes[$detectedMime])) {
                $errors[] = "Only PDF and image files (JPG, PNG, WEBP) are allowed.";
            } elseif (empty($errors)) {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = $allowedMimes[$detectedMime];
                $savedFilename = date("Ymd_His") . "_" . bin2hex(random_bytes(6)) . "." . $ext;
                $destPath = $uploadDir . DIRECTORY_SEPARATOR . $savedFilename;

                if (!move_uploaded_file($tmpPath, $destPath)) {
                    $errors[] = "Could not move uploaded file to uploads folder.";
                    $savedFilename = "";
                }
            }
        }
    }

    if (empty($errors)) {
        $isNewFile = !file_exists($logFile);
        $fp = fopen($logFile, "a");
        if ($fp && flock($fp, LOCK_EX)) {
            if ($isNewFile) {
                fputcsv($fp, ["timestamp", "name", "email", "message", "attachment"]);
            }
            fputcsv($fp, [date("Y-m-d H:i:s"), $name, $email, $message, $savedFilename]);
            flock($fp, LOCK_UN);
            fclose($fp);
            $success = "Message submitted successfully!" . ($savedFilename ? " Saved file: {$savedFilename}" : "");
            $name = $email = $message = "";
        } else {
            $errors[] = "Unable to write to log file.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
    <h3>Contact Form (Problem 11)</h3>

    <?php if ($success !== ""): ?>
        <p style="color:green">âœ… <?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul style="color:red">
            <?php foreach ($errors as $err): ?>
                <li>âŒ <?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <p>Name:<br><input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required></p>
        <p>Email:<br><input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></p>
        <p>Message:<br><textarea name="message" rows="4" cols="40" required><?= htmlspecialchars($message) ?></textarea></p>
        <p>Attachment (PDF/Image, max 2MB):<br><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp"></p>
        <button type="submit">Send Message</button>
    </form>
</body>
</html>