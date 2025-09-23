<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
#require 'db.php';
require dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Store is_admin in session if not already set
if (!isset($_SESSION['is_admin'])) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['is_admin'] = $user['is_admin'];
    }
}

$directory_id = 'Unknown'; // Default fallback

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT companies.directory_id FROM users JOIN companies ON users.company_id = companies.id WHERE users.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    if ($result && isset($result['directory_id'])) {
        $directory_id = $result['directory_id'];
    }
}


function require_admin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header('Location: no_access.php');
        exit;
    }
}