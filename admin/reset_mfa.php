<?php
date_default_timezone_set('Europe/Stockholm');
require '../db.php';
require '../assets/auth.php';
require_once '../assets/smtp.php';

$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    die("❌ Invalid user ID.");
}

// Fetch user info
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("❌ User not found.");
}

$user_name = $user['name'];
$user_email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', time() + 6 * 3600); // 6 hours from now
    $admin_id = $_SESSION['user_id'];
    $reset_time = date('Y-m-d H:i:s');
    $reason = $_POST['reason'] ?? null;

    try {
        // Invalidate old MFA and store reset token
        $stmt = $pdo->prepare("UPDATE users SET totp_enabled = 0, totp_secret = NULL, mfa_reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user_id]);

        // Log the reset
        $stmt = $pdo->prepare("INSERT INTO mfa_resets (user_id, admin_id, reset_time, reason) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $admin_id, $reset_time, $reason]);

        // Send email
        $subject = 'MFA Reset Link';
        $body = "Hi $user_name,\n\nYour MFA has been reset by an administrator.\n\nUse the link below to set up a new MFA method:\n\n<?= DEPLOYSMART_BASE_URL ?>/reset_mfa.php?token=$token\n\nThis link expires at $expiry.\n\nIf you did not request this, please contact support immediately.";
        $result = send_smtp_mail($user_email, $subject, $body);

        // Output result
        echo "<div class='content'>";
        echo "<h2>MFA Reset Token Generated</h2>";
        echo "<p>Email delivery status: $result</p>";
        echo "<p>If needed, you can manually send this link:</p>";
        echo "<code><?= DEPLOYSMART_BASE_URL ?>/reset_mfa.php?token=" . htmlspecialchars($token) . "</code>";
        echo "<p>Valid until: $expiry</p>";
        echo "<p><a href='dashboard.php'>⬅ Back to Dashboard</a></p>";
        echo "</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Failed to process MFA reset: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    // Show reset form
    echo "<div class='content'>";
    echo "<h2>Reset MFA for User: " . htmlspecialchars($user_name) . "</h2>";
    echo "<form method='POST'>";
    echo "<label>Reason for reset:</label><br>";
    echo "<input type='text' name='reason' placeholder='e.g. Lost phone'><br><br>";
    echo "<input type='submit' value='Generate Reset Token'>";
    echo "</form>";
    echo "<p><a href='users.php'>⬅ Back to Users</a></p>";
    echo "</div>";
}
?>