<?php
require 'assets/auth.php';
require 'db.php';
include('./assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
$target_directory_id = $_GET['DS'] ?? null;

if (!$user_id || !$target_directory_id) {
    die("Access denied. Missing user or company ID.");
}

// Validate access to target company
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.directory_id
    FROM companies c
    JOIN user_companies uc ON uc.company_id = c.id
    WHERE uc.user_id = ? AND c.directory_id = ?
    LIMIT 1
");
$stmt->execute([$user_id, $target_directory_id]);
$company = $stmt->fetch();

if (!$company) {
    die("Unauthorized or invalid company.");
}

$company_name = $company['name'];
$directory_id = $company['directory_id'];
$save_path = __DIR__ . "/config/$directory_id/scripts/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $script_name_raw = $_POST['script_name'] ?? '';
    $script_content = $_POST['script_content'] ?? '';

    $script_name = str_replace(' ', '_', $script_name_raw);
    $script_name = preg_replace('/[^A-Za-z0-9_\-]/', '', $script_name);
    $script_name .= '.ps1';

    if (!is_dir($save_path)) {
        if (!mkdir($save_path, 0755, true)) {
            $error = "Failed to create directory: $save_path. Check permissions.";
        }
    }

    if (empty($error)) {
        $full_path = $save_path . $script_name;
        if (file_put_contents($full_path, $script_content) !== false) {
            $message = "Script '$script_name' saved successfully!";

            // ✅ Auto-update applications.available.json
            $scriptFiles = glob($save_path . '*.ps1');
            $apps = [];
            $baseUrl = '<?= DEPLOYSMART_BASE_URL ?>/config';

            foreach ($scriptFiles as $scriptPath) {
                $name = basename($scriptPath, '.ps1');
                $apps[] = [
                    'Name' => $name,
                    'Url' => "$baseUrl/$directory_id/scripts/" . basename($scriptPath)
                ];
            }

            $jsonPath = __DIR__ . "/config/$directory_id/applications.available.json";
            file_put_contents($jsonPath, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $error = "Failed to save script. Check file permissions.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DeploySmart - Create custom script</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="content">
        <h2>Create custom script for <?= htmlspecialchars($company_name) ?></h2>

        <?php if (!empty($message)): ?>
            <p style="color: green;"><?= htmlspecialchars($message) ?></p>
        <?php elseif (!empty($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="script_name">Script name:</label><br>
            <input type="text" id="script_name" name="script_name" required><br><br>

            <label for="script_content">PowerShell code:</label><br>
            <textarea id="script_content" name="script_content" rows="15" cols="80" required></textarea><br><br>

            <button type="submit" class="btn-add">Save Script</button>
        </form>
    </div>

    <?php include('./assets/footer.php'); ?>
</body>
</html>