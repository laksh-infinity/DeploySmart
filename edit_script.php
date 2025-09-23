<?php
require 'assets/auth.php';
include 'assets/header.php';
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get current user info
$stmt = $pdo->prepare("SELECT company_id, is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$current_user = $stmt->fetch();

if (!$current_user) {
    echo '<div class="content"><h2>Access Denied</h2><p>You must be logged in.</p></div>';
    include 'assets/footer.php';
    exit;
}

// Get script name and directory
$script_name = $_GET['file'] ?? $_GET['name'] ?? null;
$script_name = urldecode($script_name);
$script_name = basename($script_name); // Prevent directory traversal

// Ensure .ps1 extension
if (!str_ends_with(strtolower($script_name), '.ps1')) {
    $script_name .= '.ps1';
}


$directory_id = $_GET['dir'] ?? null;

// If dir is not provided, resolve from company_id
if (!$directory_id && isset($_GET['company_id']) && is_numeric($_GET['company_id'])) {
    $stmt = $pdo->prepare("SELECT directory_id FROM companies WHERE id = ?");
    $stmt->execute([$_GET['company_id']]);
    $company = $stmt->fetch();

    if (!$company) {
        echo '<div class="content"><h2>Company Not Found</h2></div>';
        include 'assets/footer.php';
        exit;
    }

    $directory_id = $company['directory_id'];

    // Access control
    if (!$current_user['is_admin'] && $current_user['company_id'] != $_GET['company_id']) {
        echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to edit scripts for this company.</p></div>';
        include 'assets/footer.php';
        exit;
    }
}

// If dir is provided directly, validate access
if (isset($_GET['dir']) && !$current_user['is_admin']) {
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE directory_id = ?");
    $stmt->execute([$_GET['dir']]);
    $company = $stmt->fetch();

    if (!$company || $company['id'] != $current_user['company_id']) {
        echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to edit scripts for this company.</p></div>';
        include 'assets/footer.php';
        exit;
    }
}

// Final validation
if (!$script_name || !$directory_id) {
    echo '<div class="content"><h2>Missing Parameters</h2></div>';
    include 'assets/footer.php';
    exit;
}

$script_path = __DIR__ . "/config/" . $directory_id . "/scripts/" . $script_name;

if (!file_exists($script_path)) {
    echo '<div class="content"><h2>Script Not Found</h2>';
    echo '<p>Checked path: ' . htmlspecialchars($script_path) . '</p></div>';
    include 'assets/footer.php';
    exit;
}

// Handle saving
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_content = $_POST['script_content'] ?? '';
    file_put_contents($script_path, $new_content);
    echo "<script>alert('Script saved successfully.'); window.location.href = 'view_scripts.php?company_id=" . htmlspecialchars($_GET['company_id'] ?? '') . "';</script>";
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
            <button id="add-btn" class="btn-add" onclick="window.location.href='create-script.php'">➕ New custom script</button>
        </div>
    
    
        <textarea name="script_content" id="script_content" rows="20" style="width:100%;"><?= htmlspecialchars($content) ?></textarea><br><br>
        <button type="submit" style="display:none;" class="btn-save">💾 Save Changes</button>
    </form>
</div>

<?php include 'assets/footer.php'; ?>
