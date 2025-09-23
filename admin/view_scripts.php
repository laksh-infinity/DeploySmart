<?php
require '../assets/auth.php';
// Get current user info
$stmt = $pdo->prepare("SELECT company_id, is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$current_user = $stmt->fetch();

if (!$current_user) {
    echo '<div class="content"><h2>Access Denied</h2><p>You must be logged in to view scripts.</p></div>';
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$company_id = $_GET['company_id'] ?? null;

if (!$company_id || !is_numeric($company_id)) {
    echo '<div class="content"><h2>Invalid Company ID</h2></div>';
    exit;
}

// Fetch company info
$stmt = $pdo->prepare("SELECT name, directory_id FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$company = $stmt->fetch();

// Access control: only admins or users from the same company
if (!$current_user['is_admin'] && $current_user['company_id'] != $company_id) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to view scripts for this company.</p></div>';
    exit;
}

if (!$company) {
    echo '<div class="content"><h2>Company Not Found</h2></div>';
    exit;
}

$script_folder = dirname(__DIR__) . "/config/" . $company['directory_id'] . "/scripts";
$scripts = [];

if (is_dir($script_folder)) {
    $scripts = glob($script_folder . "/*.ps1");
}
?>

<?php include '../assets/header.php'; ?>
<div class="content">
    <h2>Scripts for <?= htmlspecialchars($company['name']) ?></h2>

    <?php if (empty($scripts)): ?>
        <p>No scripts found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($scripts as $script_path): 
                $script_name = basename($script_path); ?>
                <li>
                    <?= htmlspecialchars($script_name) ?>
                    — <a href="edit_script.php?company_id=<?= $company_id ?>&file=<?= urlencode($script_name) ?>">Edit</a>
                    — <a href="delete_script.php?company_id=<?= $company_id ?>&file=<?= urlencode($script_name) ?>" onclick="return confirm('Delete this script?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php include '../assets/footer.php'; ?>