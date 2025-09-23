<?php
require 'db.php';

$token = $_GET['token'] ?? '';
if (strlen($token) !== 64) {
    die("❌ Invalid token.");
}

$stmt = $pdo->prepare("SELECT id, reset_token_expiry FROM users WHERE mfa_reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user || strtotime($user['reset_token_expiry']) < time()) {
    die("❌ Token expired or invalid.");
}

// Reset MFA fields
$stmt = $pdo->prepare("UPDATE users SET totp_enabled = 0, totp_secret = NULL, mfa_reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

echo "<div class='content'>";
echo "<h2>MFA Reset Successful</h2>";
echo "<p>You can now reconfigure your MFA settings.</p>";
echo "</div>";
?>