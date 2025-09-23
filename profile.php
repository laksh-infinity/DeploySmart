<?php
require 'assets/auth.php';
require 'assets/totp_functions.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo '<div class="content"><h2>Access Denied</h2><p>Please log in to access your profile.</p></div>';
    exit;
}

include 'assets/header.php';

// Fetch user data
$stmt = $pdo->prepare("SELECT email, name, surname, password_hash, totp_enabled, totp_secret FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo '<div class="content"><h2>User Not Found</h2></div>';
    include 'assets/footer.php';
    exit;
}

$message = '';
$error = '';
$totp_message = '';
$totp_error = '';
$qrImage = '';
$totp_secret = '';

function isStrongPassword($password) {
    return strlen($password) >= 12 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password) &&
           preg_match('/[\W_]/', $password);
}

function generate_base32_secret($length = 16) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $secret;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_update'])) {
    $new_email = $_POST['email'] ?? '';
    $new_password = $_POST['password'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_name = $_POST['name'] ?? '';
    $new_surname = $_POST['surname'] ?? '';

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!password_verify($current_password, $user['password_hash'])) {
        $error = "Current password is incorrect.";
    } elseif (!empty($new_password) && !isStrongPassword($new_password)) {
        $error = "Password must be at least 12 characters long and include uppercase, lowercase, number, and special character.";
    } else {
        try {
            if ($new_email !== $user['email']) {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$new_email, $user_id]);
                $stmt = $pdo->prepare("INSERT INTO profile_changes (user_id, change_type, old_value, new_value) VALUES (?, 'email', ?, ?)");
                $stmt->execute([$user_id, $user['email'], $new_email]);
            }

            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$hashed, $user_id]);
                $stmt = $pdo->prepare("INSERT INTO profile_changes (user_id, change_type, old_value, new_value) VALUES (?, 'password', ?, ?)");
                $stmt->execute([$user_id, '[hidden]', '[updated]']);
            }

            if ($new_name !== $user['name']) {
                $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->execute([$new_name, $user_id]);
            }

            if ($new_surname !== $user['surname']) {
                $stmt = $pdo->prepare("UPDATE users SET surname = ? WHERE id = ?");
                $stmt->execute([$new_surname, $user_id]);
            }

            $message = "Profile updated successfully.";
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Handle TOTP actions
$totp_setup_mode = false;
$totp_just_enabled = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['totp_action'])) {
    $action = $_POST['totp_action'];

    if ($action === 'enable') {
        // Begin setup
        $totp_secret = generate_base32_secret();
        $qrUrl = "otpauth://totp/DeploySmart:" . urlencode($user['email']) . "?secret=$totp_secret&issuer=DeploySmart";
        $qrImage = "https://quickchart.io/qr?text=" . urlencode($qrUrl) . "&size=200";
        $totp_setup_mode = true;

    } elseif ($action === 'confirm_enable') {
        // Verify code
        $inputCode = trim($_POST['totp_code'] ?? '');
        $totp_secret = $_POST['totp_secret'] ?? '';
        $valid = false;

        for ($i = -1; $i <= 1; $i++) {
            if (generate_totp($totp_secret, floor(time() / 30) + $i) === $inputCode) {
                $valid = true;
                break;
            }
        }

        if ($valid) {
            $stmt = $pdo->prepare("UPDATE users SET totp_enabled = 1, totp_secret = ? WHERE id = ?");
            $stmt->execute([$totp_secret, $user_id]);
            $totp_message = "✅ Two-factor authentication enabled.";
            $totp_just_enabled = true;
            $user['totp_enabled'] = 1; // Update local state
        } else {
            $totp_error = "❌ Invalid code. Please try again.";
            $qrUrl = "otpauth://totp/DeploySmart:" . urlencode($user['email']) . "?secret=$totp_secret&issuer=DeploySmart";
            $qrImage = "https://quickchart.io/qr?text=" . urlencode($qrUrl) . "&size=200";
            $totp_setup_mode = true;
        }

    } elseif ($action === 'disable') {
        $stmt = $pdo->prepare("UPDATE users SET totp_enabled = 0, totp_secret = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        $totp_message = "🔒 Two-factor authentication disabled.";
        $user['totp_enabled'] = 0; // Update local state
    }
}

// Fetch login history
$stmt = $pdo->prepare("SELECT timestamp, ip_address, user_agent FROM login_history WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
$stmt->execute([$user_id]);
$logins = $stmt->fetchAll();

// Fetch profile changes
$stmt = $pdo->prepare("SELECT change_type, old_value, new_value, timestamp FROM profile_changes WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
$stmt->execute([$user_id]);
$changes = $stmt->fetchAll();
?>
<div class="content">
    <h2>👤 Your Profile</h2>

    <?php if ($message): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- LEFT COLUMN -->
        <div class="profile-left">
            <h3>🔐 Profile Settings</h3>
            <form method="POST">
                <input type="hidden" name="profile_update" value="1">

                <label>First Name:</label><br>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"><br><br>

                <label>Last Name:</label><br>
                <input type="text" name="surname" value="<?= htmlspecialchars($user['surname']) ?>"><br><br>

                <label>Email:</label><br>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br><br>

                <label>Current Password:</label><br>
                <input type="password" name="current_password" required><br><br>

                <label>New Password (leave blank to keep current):</label><br>
                <input type="password" name="password"><br>
                <small>Password must be ≥12 chars, include uppercase, lowercase, number, and special character.</small><br><br>

                <button type="submit" class="btn-save">💾 Save Changes</button>
            </form>

            <h3>🛡️ Two-Factor Authentication</h3>

<?php if ($totp_message): ?>
    <p style="color: green;"><?= htmlspecialchars($totp_message) ?></p>
<?php endif; ?>

<?php if (!$user['totp_enabled']): ?>
    <div class="totp-warning">
        <p style="color: #b30000; font-weight: bold;">
            ⚠️ Your account is not protected by two-factor authentication.
        </p>
        <p style="color: #b30000;">
            <span style="font-size: 1.2em;">🧿❌</span> This means your account is more vulnerable to unauthorized access.
        </p>
    </div>
    <form action="enable_totp.php" method="get" style="margin-top: 10px;">
    	<button type="submit" class="btn-add">🛡️ Activate TOTP</button>
	</form>
<?php else: ?>
    <p>✅ TOTP is currently <strong>enabled</strong> on your account.</p>
    <form method="POST">
        <input type="hidden" name="totp_action" value="disable">
        <button type="submit" class="btn-del">Disable TOTP</button>
    </form>
<?php endif; ?>
        </div>
        <!-- RIGHT COLUMN -->
        <div class="profile-right">
            <h3>🕵️ Recent Logins</h3>
            <ul>
                <?php foreach ($logins as $login): ?>
                    <li><?= $login['timestamp'] ?> — <?= htmlspecialchars($login['ip_address']) ?> — <?= htmlspecialchars($login['user_agent']) ?></li>
                <?php endforeach; ?>
            </ul>

            <h3>📜 Profile Change History</h3>
            <ul>
                <?php foreach ($changes as $change): ?>
                    <li><?= $change['timestamp'] ?> — <?= ucfirst($change['change_type']) ?> changed from <strong><?= htmlspecialchars($change['old_value']) ?></strong> to <strong><?= htmlspecialchars($change['new_value']) ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php include 'assets/footer.php'; ?>
