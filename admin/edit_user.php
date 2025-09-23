<?php
require '../assets/auth.php';
include('../assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check admin access
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

if (!$current_user || $current_user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to view this page.</p></div>';
    include('../assets/footer.php');
    exit;
}

// Get user ID
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) {
    echo '<div class="content"><h2>Error</h2><p>Invalid user ID.</p></div>';
    include('../assets/footer.php');
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo '<div class="content"><h2>Error</h2><p>User not found.</p></div>';
    include('../assets/footer.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $company_id = intval($_POST['company_id']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $totp_enabled = isset($_POST['totp_enabled']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, email = ?, phone = ?, company_id = ?, is_admin = ?, totp_enabled = ? WHERE id = ?");
    $stmt->execute([$name, $surname, $email, $phone, $company_id, $is_admin, $totp_enabled, $user_id]);

    echo "<div class='content'><h2>Success</h2><p>User updated successfully.</p><button class='btn-add' type='button' onclick=\"window.location.href='users.php'\">← Back to Users</button></div>";
    include('../assets/footer.php');
    exit;
}

// Fetch companies for dropdown
$companies = $pdo->query("SELECT id, name FROM companies ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="sv">
<body>
<div class="content">
    <h2>Edit User</h2>
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>

        <label>Surname:</label><br>
        <input type="text" name="surname" value="<?= htmlspecialchars($user['surname']) ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"><br><br>

        <label>Company:</label><br>
        <select name="company_id">
            <option value="0">❌ None</option>
            <?php foreach ($companies as $company): ?>
                <option value="<?= $company['id'] ?>" <?= $company['id'] == $user['company_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($company['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label><input type="checkbox" name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>> Admin</label><br>
        <label><input type="checkbox" name="totp_enabled" <?= $user['totp_enabled'] ? 'checked' : '' ?>> MFA Enabled</label><br><br>

        <button class="btn-add" type="submit">💾 Save Changes</button>
        <button class="btn-del" type="button" onclick="window.location.href='users.php'">← Cancel</button>
    </form>
</div>
</body>
<?php include('../assets/footer.php'); ?>
</html>