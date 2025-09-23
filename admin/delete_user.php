<?php
require '../assets/auth.php'; // ensures session is started and user is authenticated

// Check if user is logged in and is an admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to delete users.</p></div>';
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

// Get user ID
$user_id = $_GET['user_id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    echo '<p style="color:red;">Missing or invalid user ID.</p>';
    exit;
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo '<p style="color:red;">User not found.</p>';
        exit;
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    // Redirect back to dashboard
    header("Location: ./dashboard.php");
    exit;

} catch (PDOException $e) {
    echo '<p style="color:red;">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}