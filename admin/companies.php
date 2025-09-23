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
    exit;
}

// Fetch all companies
$companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll();

// Fetch all users grouped by company
$users_by_company = [];
$stmt = $pdo->query("SELECT * FROM users ORDER BY company_id");
while ($user = $stmt->fetch()) {
    $users_by_company[$user['company_id']][] = $user;
}
?>

<!DOCTYPE html>
<html lang="sv">
<body>
  <div class="content">
    <h2>Admin Dashboard</h2>
    <p><a href="register_company.php">➕ Register New Company</a></p>

    <?php foreach ($companies as $company): ?>
    <?php
    $script_folder = dirname(__DIR__) . "/config/" . $company['directory_id'] . "/scripts";
    $script_count = is_dir($script_folder) ? count(glob($script_folder . "/*.ps1")) : 0;
    ?>
    <div class="company-card">
        <div class="company-header">
            <h2><?= htmlspecialchars($company['name']) ?></h2>
            <span class="deployment-id">Deployment ID: <?= htmlspecialchars($company['directory_id']) ?></span>
        </div>

        <div class="company-actions">
            <a class="action-link" href="delete_company.php?company_id=<?= $company['id'] ?>" onclick="return confirm('Are you sure you want to delete this company and all its users?');">🗑️ Delete Company</a>
            <a class="action-link" href="register_user.php?company_id=<?= $company['id'] ?>">➕ Register User</a>
        </div>

        <div class="company-flex">
            <div class="user-list">
                <h4>👥 Users:</h4>
                <ul>
                    <?php if (!empty($users_by_company[$company['id']])): ?>
                        <?php foreach ($users_by_company[$company['id']] as $user): ?>
                            <li>
                                <?= htmlspecialchars($user['name']) ?> <?= htmlspecialchars($user['surname']) ?> — <?= htmlspecialchars($user['email']) ?>
                                <a class="action-link" style="float: right;" href="delete_user.php?user_id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">🗑️ Delete</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No users registered.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="script-info">
                <h4>📜 Scripts:</h4>
                <p>
                    📝 Custom Scripts: <strong><?= $script_count ?></strong>
                    <?php if ($script_count > 0): ?>
                        — <a class="action-link" href="view_scripts.php?company_id=<?= $company['id'] ?>">View Scripts</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
<?php endforeach; ?>
  </div>
</body>
<?php require '../assets/footer.php'; ?>
</html>