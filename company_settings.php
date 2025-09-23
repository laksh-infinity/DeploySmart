<?php
require 'assets/auth.php';
include('./assets/header.php'); 
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get current user's company
$stmt = $pdo->prepare("SELECT company_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo '<div class="content"><h2>Error</h2><p>User not found.</p></div>';
    exit;
}

$company_id = $user['company_id'];

// Fetch the company info
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$company = $stmt->fetch();

if (!$company) {
    echo '<div class="content"><h2>Error</h2><p>Company not found.</p></div>';
    exit;
}

// Fetch users from this company
$stmt = $pdo->prepare("SELECT * FROM users WHERE company_id = ?");
$stmt->execute([$company_id]);
$users = $stmt->fetchAll();

// Count scripts
$script_folder = __DIR__ . "/config/" . $company['directory_id'] . "/scripts";
$script_count = 0;

if (is_dir($script_folder)) {
    $files = glob($script_folder . "/*.ps1");
    $script_count = count($files);
}
?>

<!DOCTYPE html>
<html lang="sv">
<body>
  <div class="content">
    <h2>Company Settings</h2>

    <div class="comp_box">
        <h3><?= htmlspecialchars($company['name']) ?></h3>
        <p style="float:right;">Deployment ID: <?= htmlspecialchars($company['directory_id']) ?></p><br><br><br>
        <p><a href="register_user.php?company_id=<?= $company['id'] ?>">➕ Register User for this Company</a></p>

        <div class="company-flex">
            <div class="user-list">
                <h4>Users:</h4>
                <ul>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <li>
                                <?= htmlspecialchars($user['name']) ?> <?= htmlspecialchars($user['surname']) ?> — <?= htmlspecialchars($user['email']) ?> (<?= htmlspecialchars($user['phone']) ?>)
                                <a style="margin-right: 10px; float: right;" href="edit_user.php?user_id=<?= $user['id'] ?>">✏️ Edit</a>
                                <a style="float: right" href="assets/delete_user.php?user_id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">🗑️ Delete</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No users registered.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="script-info">
                <h4>Scripts:</h4>
                <p>
                    📝 Custom Scripts: <?= $script_count ?>
                    <?php if ($script_count > 0): ?>
                        — <a href="view_scripts.php?company_id=<?= $company['id'] ?>">View Scripts</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
  </div>
</body>
<?php require './assets/footer.php'; ?>
</html>
