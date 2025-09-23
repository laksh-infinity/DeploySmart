<?php
require 'assets/auth.php';
require 'db.php';
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

								
$configPath = __DIR__ . "/config/" . $directory_id;
$jsonFile = $configPath . "/applications.json";

									   
if (!is_dir($configPath)) {
    mkdir($configPath, 0755, true);
}

													 
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DeploySmart - Configure2</title>
    <?php include('./assets/header.php'); ?>
</head>
<body>
    <div class="container">
        <div class="actions">
            <button id="save-btn" class="btn-save">💾 Save</button>
            <button id="reset-btn" class="btn-reset">🔁 Reset</button>
            <button id="add-btn" class="btn-add" onclick="window.location.href='create-script.php'">➕ New custom script</button>
        </div>

        <div class="split-view">
            <div class="left-panel">
                <h2>Available Applications</h2>
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('deployment')">🌐 Global</button>
                    <button class="tab-btn" onclick="switchTab('company')">📝 Custom scripts</button>
                </div>
                <ul id="available-list-deployment" class="available-list"></ul>
                <ul id="available-list-company" class="available-list" style="display:none;"></ul>
            </div>

            <div class="right-panel">
                <h2>Selected Applications</h2>
                <div class="tab-btn active">🧬 Selected</div>
                <ul id="selected-list"></ul>
            </div>
        </div>
    </div>

    <?php include('./assets/footer.php'); ?>

    <script>
        const directoryId = "<?php echo $directory_id; ?>";
    </script>
    <script src="./assets/app2.js"></script>
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