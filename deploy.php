<?php
require_once('db.php');

// Get and sanitize parameters
$directory_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['ID'] ?? '');
$deploysmart_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['DS'] ?? '');

// Prevent dual parameter usage
if ($directory_id && $deploysmart_id) {
    http_response_code(400);
    echo json_encode(["error" => "Provide either ID or DS, not both"]);
    exit;
}

// Determine ID and file type
if ($directory_id) {
    $id = $directory_id;
    $file = 'applications.json';
    $content_type = 'application/json';
    $method = 'iso';
} elseif ($deploysmart_id) {
    $id = $deploysmart_id;
    $file = 'install.ps1';
    $content_type = 'text/plain';
    $method = 'powershell';
} else {
    http_response_code(400);
    echo json_encode(["error" => "Missing ID or DS parameter"]);
    exit;
}

// Validate company existence and get ID
$stmt = $pdo->prepare("SELECT id FROM companies WHERE directory_id = ?");
$stmt->execute([$id]);
$company_id = $stmt->fetchColumn();

if (!$company_id) {
    http_response_code(404);
    echo json_encode(["error" => "Invalid ID"]);
    exit;
}

// Determine file path
$base_path = __DIR__ . "/config/$id/";
$full_path = $base_path . $file;

if (!file_exists($full_path)) {
    http_response_code(404);
    echo json_encode(["error" => "$file not found"]);
    exit;
}

// Serve file
header("Content-Type: $content_type");
readfile($full_path);

// ✅ Log deployment only on GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("INSERT INTO deployments (company_id, method) VALUES (?, ?)");
    $stmt->execute([$company_id, $method]);
}

exit;
?>