<?php
require 'assets/auth.php';
require 'db.php';
include 'assets/header.php';
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
$directory_id = $_GET['dir'] ?? null;
$script_name = $_GET['name'] ?? $_GET['file'] ?? null;

if (!$user_id || !$directory_id || !$script_name) {
    echo '<div class="content"><h2>Missing Parameters</h2><p>Script name or directory ID is missing.</p></div>';
    include 'assets/footer.php';
    exit;
}

// Sanitize script name
$script_name = basename(urldecode($script_name));
if (!str_ends_with(strtolower($script_name), '.ps1')) {
    $script_name .= '.ps1';
}

// Validate access to company
$stmt = $pdo->prepare("
    SELECT c.id, c.name
    FROM companies c
    JOIN user_companies uc ON uc.company_id = c.id
    WHERE uc.user_id = ? AND c.directory_id = ?
    LIMIT 1
");
$stmt->execute([$user_id, $directory_id]);
$company = $stmt->fetch();

if (!$company) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to edit scripts for this company.</p></div>';
    include 'assets/footer.php';
    exit;
}

$company_name = $company['name'];
$script_path = __DIR__ . "/config/$directory_id/scripts/$script_name";

if (!file_exists($script_path)) {
    echo '<div class="content"><h2>Script Not Found</h2><p>Checked path: ' . htmlspecialchars($script_path) . '</p></div>';
    include 'assets/footer.php';
    exit;
}

// Handle saving
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_content = $_POST['script_content'] ?? '';
    file_put_contents($script_path, $new_content);
    echo "<script>alert('Script saved successfully.'); window.location.href = 'configure_apps.php?DS=" . htmlspecialchars($directory_id) . "';</script>";
    exit;
}

// Load content
$content = file_get_contents($script_path);
?>

<h2 style="margin-left:20px;">Edit Script: <?= htmlspecialchars($script_name) ?></h2>
<form method="post">
    <div class="container">
        <div class="actions">
            <button type="submit" class="btn-save">💾 Save Changes</button>
            <button id="add-btn" class="btn-add" onclick="window.location.href='company-script.php?DS=<?= htmlspecialchars($directory_id) ?>'">➕ New custom script</button>
        </div>

        <textarea name="script_content" id="script_content" rows="20" style="width:100%;"><?= htmlspecialchars($content) ?></textarea><br><br>
        <button type="submit" style="display:none;" class="btn-save">💾 Save Changes</button>
    </div>
</form>

<?php include 'assets/footer.php'; ?>