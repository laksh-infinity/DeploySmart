<?php
require 'assets/auth.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$company_id = $_GET['company_id'] ?? null;
$file = $_GET['file'] ?? null;

if (!$company_id || !$file || !is_numeric($company_id)) {
    echo '<div class="content"><h2>Invalid Request</h2></div>';
    exit;
}

$stmt = $pdo->prepare("SELECT directory_id FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$company = $stmt->fetch();

if (!$company) {
    echo '<div class="content"><h2>Company Not Found</h2></div>';
    exit;
}

$script_path = __DIR__ . "/config/" . $company['directory_id'] . "/scripts/" . basename($file);

if (file_exists($script_path)) {
    unlink($script_path);
}

header("Location: view_scripts.php?company_id=" . $company_id);
exit;