<?php
require 'assets/auth.php';
require 'db.php';

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Access denied. Please log in.");
}

// Determine target company via ?DS=... or fallback
$target_directory_id = $_GET['DS'] ?? null;
$company_id = null;

if ($target_directory_id) {
    $stmt = $pdo->prepare("
        SELECT c.id, c.directory_id
        FROM companies c
        JOIN user_companies uc ON uc.company_id = c.id
        WHERE uc.user_id = ? AND c.directory_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id, $target_directory_id]);
    $company = $stmt->fetch();

    if (!$company) {
        die("Invalid or unauthorized company ID.");
    }

    $target_directory_id = $company['directory_id'];
    $company_id = $company['id'];
} else {
    $stmt = $pdo->prepare("
        SELECT c.id, c.directory_id
        FROM companies c
        JOIN user_companies uc ON uc.company_id = c.id
        WHERE uc.user_id = ?
        ORDER BY c.name ASC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $company = $stmt->fetch();

    if (!$company) {
        die("No companies linked to your account.");
    }

    $target_directory_id = $company['directory_id'];
    $company_id = $company['id'];
}

// Build correct config paths
$configPath = __DIR__ . "/config/" . $target_directory_id;
$scriptsPath = $configPath . "/scripts";
$jsonFile = $configPath . "/applications.json";
$availableFile = $configPath . "/applications.available.json";

// Ensure folders and files exist
if (!is_dir($configPath)) {
    mkdir($configPath, 0755, true);
}
if (!is_dir($scriptsPath)) {
    mkdir($scriptsPath, 0755, true);
}
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
if (!file_exists($availableFile)) {
    file_put_contents($availableFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DeploySmart - Configure Company</title>
    <?php include('./assets/header.php'); ?>
</head>
<body>
    <div class="container">
        <div class="actions">
            <button id="save-btn" class="btn-save">💾 Save</button>
            <button id="reset-btn" class="btn-reset">🔁 Reset</button>
            <button id="add-btn" class="btn-add" onclick="window.location.href='company-script.php?DS=<?= htmlspecialchars($target_directory_id) ?>'">➕ New custom script</button>
        </div>

        <div class="split-view">
            <div class="left-panel">
                <h2>Available Applications</h2>
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('deployment')">Global</button>
                    <button class="tab-btn" onclick="switchTab('company')">Custom scripts</button>
                </div>
                <ul id="available-list-deployment" class="available-list"></ul>
                <ul id="available-list-company" class="available-list" style="display:none;"></ul>
            </div>

            <div class="right-panel">
                <h2>Selected Applications</h2>
                <div class="tab-btn active">Selected</div>
                <ul id="selected-list"></ul>
            </div>
        </div>
    </div>

    <?php include('./assets/footer.php'); ?>

    <script>
	    window.directoryId = "<?php echo htmlspecialchars($target_directory_id); ?>";
	</script>
	<script>
		const DEPLOYSMART_BASE_URL = "<?= DEPLOYSMART_BASE_URL ?>";
	</script>

	<script src="/assets/configureCompany.js"></script>

    <div id="confirm-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <p id="confirm-message">Are you sure you want to delete this script?</p>
            <div class="modal-actions">
                <button id="confirm-yes" class="btn-del">Yes, delete</button>
                <button id="confirm-no" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>
</body>
</html>