<?php
require '../assets/auth.php';
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is an admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to perform this action.</p></div>';
    exit;
}

// Validate and save page data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['page_data'])) {
    $json = $_POST['page_data'];

    // Optional: validate JSON structure
    $blocks = json_decode($json, true);
    if (!is_array($blocks)) {
        echo '<div class="content"><h2>Error</h2><p>Invalid page data format.</p></div>';
        exit;
    }

    // Save to file
    $savePath = '../content/page.json';
    file_put_contents($savePath, json_encode($blocks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Redirect back to editor
    header("Location: edit.php?saved=1");
    exit;
} else {
    echo '<div class="content"><h2>Error</h2><p>No page data received.</p></div>';
    exit;
}
?>