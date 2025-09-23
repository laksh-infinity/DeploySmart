<?php
session_start();
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Check if TOTP is enabled
        if (!empty($user['totp_enabled']) && $user['totp_enabled'] == 1) {
            // Store minimal session data until TOTP is verified
            $_SESSION['pending_2fa'] = $user['id'];
            $_SESSION['pending_email'] = $user['email']; // Optional, for display

            // Log login attempt (pre-verification)
            $stmt = $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $stmt->execute([
                $user['id'],
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            header('Location: verify_totp.php');
            exit;
        }

        // No TOTP, proceed to full login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['company_id'] = $user['company_id'];

        // Log login event
        $stmt = $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
        $stmt->execute([
            $user['id'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        header('Location: dashboard.php');
        exit;
    } else {
        $message = "❌ Invalid credentials.";
    }
}
?>

<?php include './assets/header.php'; ?>
<div class="container">
<div class="login-container">
    <form method="POST" action="">
        <h2>Login</h2>
        <p>To continue using our services, please sign in.</p>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <table style="margin-bottom:20px;">
            <tr><td style="float:right;">Email:</td><td><input type="text" name="email" placeholder="Email" required></td></tr>
            <tr><td>Password:</td><td><input type="password" name="password" placeholder="Password" required></td></tr>
        </table>
        <button class="btn-add" type="submit">Login</button>
        <a href="#" class="btn-del" style="text-decoration: none; font-size:14px; margin-top: 0px; padding: 3px 10px; display: block; float: left; align-items: center; border: none; border-radius: 6px; cursor: pointer; color: #fff; margin-left: 5px; margin-top: -3px;">Forgot password?</a>
    </form>
</div>
</div>

<?php include './assets/footer.php'; ?>