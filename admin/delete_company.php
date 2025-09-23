<?php
require '../assets/auth.php'; // ensures session is started and user is authenticated

// Check if user is logged in and is an admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to delete companies.</p></div>';
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo '<p style="color:red;">Invalid request method.</p>';
    exit;
}

// Get company ID
$company_id = $_GET['company_id'] ?? null;

if (!$company_id || !is_numeric($company_id)) {
    echo '<p style="color:red;">Missing or invalid company ID.</p>';
    exit;
}

try {
    // Fetch company
    $stmt = $pdo->prepare("SELECT directory_id FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch();

    if (!$company) {
        echo '<p style="color:red;">Company not found.</p>';
        exit;
    }

    $directory_id = $company['directory_id'];

    // Begin transaction
    $pdo->beginTransaction();

    // Delete users
    $stmt = $pdo->prepare("DELETE FROM users WHERE company_id = ?");
    $stmt->execute([$company_id]);

    // Delete company
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);

    $pdo->commit();

    // Delete config folder
    $folderPath = dirname(__DIR__) . "/config/$directory_id";
    if (is_dir($folderPath)) {
        function deleteFolder($path) {
            foreach (glob($path . '/*') as $file) {
                is_dir($file) ? deleteFolder($file) : unlink($file);
            }
            rmdir($path);
        }
        deleteFolder($folderPath);
    }

    // Redirect
    header("Location: ./dashboard.php");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    echo '<p style="color:red;">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}