<?php
require 'assets/auth.php';
require 'assets/totp_functions.php';
include('./assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';
$error = '';
$qrImage = '';
$secret = '';

function generate_base32_secret($length = 16) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $secret;
}

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT email, totp_enabled FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$email = $user['email'];
$issuer = "DeploySmart";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputCode = trim($_POST['totp_code'] ?? '');
    $secret = $_POST['secret'] ?? '';

    $valid = false;
    for ($i = -1; $i <= 1; $i++) {
        if (generate_totp($secret, floor(time() / 30) + $i) === $inputCode) {
            $valid = true;
            break;
        }
    }

    if ($valid) {
        $stmt = $pdo->prepare("UPDATE users SET totp_enabled = 1, totp_secret = ? WHERE id = ?");
        $stmt->execute([$secret, $userId]);
        $message = "✅ TOTP activated successfully!";
    } else {
        $error = "❌ Invalid code. Please try again.";
    }
} else {
    $secret = generate_base32_secret();
    $qrUrl = "otpauth://totp/$issuer:$email?secret=$secret&issuer=$issuer";
    $qrImage = "https://quickchart.io/qr?text=" . urlencode($qrUrl) . "&size=200";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DeploySmart - Enable Two-Factor Authentication</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="content enable-qr">
        <h2>Enable Two-Factor Authentication</h2>

        <?php if (!empty($message)): ?>
            <p style="color: green;"><?= htmlspecialchars($message) ?></p>
        <?php elseif (!empty($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (empty($message)): ?>
            <p>Scan this QR code with your authenticator app:</p>
            <img src="<?= $qrImage ?>" alt="QR Code"><br><br>

            <form method="POST">
                <label for="totp_code">Enter the 6-digit code from your app:</label><br>
                <input type="text" id="totp_code" name="totp_code" maxlength="6" required><br><br>
                <input type="hidden" name="secret" value="<?= $secret ?>">
                <button type="submit" class="btn-add">Activate TOTP</button>
            </form>
        <?php endif; ?>
    </div>

    <?php include('./assets/footer.php'); ?>
</body>
</html>