<?php
#require 'assets/auth.php';
include('./assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require './assets/totp_functions.php';
require 'db.php';

if (!isset($_SESSION['pending_2fa'])) {
    die("Access denied. Please log in.");
}

$userId = $_SESSION['pending_2fa'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || empty($user['totp_secret'])) {
    die("User or TOTP secret not found.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputCode = trim($_POST['totp_code'] ?? '');
    $valid = false;

    for ($i = -1; $i <= 1; $i++) {
        if (generate_totp($user['totp_secret'], floor(time() / 30) + $i) === $inputCode) {
            $valid = true;
            break;
        }
    }

    if ($valid) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['company_id'] = $user['company_id'];
        unset($_SESSION['pending_2fa']);
        header("Location: dashboard.php");
        exit;
    } else {
        $message = "❌ Invalid code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DeploySmart - Verify TOTP</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="content">
        <h2>Two-Factor Authentication</h2>

        <?php if (!empty($message)): ?>
            <p style="color: red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="totp_code">Enter your 6-digit code:</label><br>
            <input type="text" id="totp_code" name="totp_code" maxlength="6" required><br><br>
            <button type="submit" class="btn-add">Verify</button>
        </form>
    </div>

    <?php include('./assets/footer.php'); ?>
</body>
</html>