<?php
require '../db.php';
require '../assets/auth.php';
require_once '../assets/smtp.php';

// ✅ Restrict to admins only
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo "<div class='content'><h2>Access Denied</h2><p>You do not have permission to access this page.</p></div>";
    exit;
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['email'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color:red;'>❌ Invalid email address.</p>";
    } else {
        $subject = 'SMTP Test Message';
        $body = "Hello!\n\nThis is a test message from your DeploySmart SMTP setup.\n\nIf you're reading this, your SMTP connection is working!";

        $result = send_smtp_mail($to, $subject, $body);
        echo "<p><strong>Result:</strong> $result</p>";
    }
}

?>

<div class="content">
    <h2>SMTP Test Tool</h2>
    <form method="POST">
        <label>Enter your email address:</label><br>
        <input type="email" name="email" required><br><br>
        <input type="submit" value="Send Test Email">
    </form>
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>
</div>

