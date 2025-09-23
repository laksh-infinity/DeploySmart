<?php
require '../assets/auth.php';
include('../assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is an admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to view this page.</p></div>';
    include('../assets/footer.php');
    exit;
}

// Handle search input
$search = trim($_GET['search'] ?? '');
$params = [];
$search_clause = '';

if ($search !== '') {
    $search_clause = "WHERE u.name LIKE ? OR u.surname LIKE ? OR u.email LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Fetch users with company names
$sql = "
    SELECT u.id, u.email, u.name, u.surname, u.phone, u.is_admin, u.totp_enabled, c.name AS company_name
    FROM users u
    LEFT JOIN companies c ON u.company_id = c.id
    $search_clause
    ORDER BY u.name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sv">
<body>
<div class="content">
    <h2>User Management Dashboard</h2>

    <form method="GET" style="margin-bottom: 20px;">
        <input id="searchbar" type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn-add" type="submit">🔍 Search</button>
    </form>

    <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Name:</th>
                <th>Email:</th>
                <th>Phone:</th>
                <th>Company:</th>
                <th>Admin:</th>
                <th>MFA:</th>
                <th>Actions:</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td><?= htmlspecialchars($user['company_name'] ?? '❌') ?></td>
                        <td><?= $user['is_admin'] ? '✅' : '❌' ?></td>
                        <td><?= $user['totp_enabled'] ? '✅' : '❌' ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id'] ?>">✏️ Edit</a> |
                            <a href="reset_mfa.php?id=<?= $user['id'] ?>" onclick="return confirm('Reset MFA for this user?');">🔁 Reset MFA</a> |
                            <a href="assets/delete_user.php?user_id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
<?php include('../assets/footer.php'); ?>
</html>