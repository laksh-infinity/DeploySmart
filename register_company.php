<?php
require 'assets/auth.php';
require 'db.php';
require 'assets/header.php';

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

// Function to create install.ps1 with updated ID
function createInstallScriptForCompany($directory_id) {
    $templatePath = __DIR__ . '/templates/install.ps1';
    $targetDir = __DIR__ . "/config/$directory_id";
    $targetPath = "$targetDir/install.ps1";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $lines = file($templatePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new Exception("Failed to read template file: $templatePath");
    }

    while (count($lines) < 8) {
        $lines[] = '';
    }

    $lines[7] = '$ID = "' . $directory_id . '"';

    $result = file_put_contents($targetPath, implode(PHP_EOL, $lines));
    if ($result === false) {
        throw new Exception("Failed to write to target file: $targetPath");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $directory_id = uniqid('ds_');
    $user_id = $_SESSION['user_id'] ?? 0;

    if (empty($name)) {
        $message = "Company name required.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert company
            $stmt = $pdo->prepare("INSERT INTO companies (name, address, phone, directory_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $address, $phone, $directory_id]);
            $company_id = $pdo->lastInsertId();

            // Link user to company as owner
            $stmt = $pdo->prepare("INSERT INTO user_companies (user_id, company_id, role) VALUES (?, ?, 'owner')");
            $stmt->execute([$user_id, $company_id]);

            // Create config folder and files
            $directory_path = dirname(__DIR__) . "/config/$directory_id";
            if (!is_dir($directory_path)) {
                mkdir($directory_path, 0777, true);
            }

            file_put_contents($directory_path . "/applications.json", json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            createInstallScriptForCompany($directory_id);

            $pdo->commit();
            $message = "Company has been registered!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Script error: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>Register new company</h2>

    <?php if (!empty($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="name">Company Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="address">Adress:</label>
        <input type="text" name="address" id="address">

        <label for="phone">Phone:</label>
        <input type="text" name="phone" id="phone">

        <button type="submit">Register</button>
    </form>
</div>

<?php require 'assets/footer.php'; ?>