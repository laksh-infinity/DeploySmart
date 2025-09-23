<?php
require '../db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Decode incoming JSON
$input = json_decode(file_get_contents("php://input"), true);
$target_directory_id = $input['directoryId'] ?? null;
$config = $input['config'] ?? [];

if (!$target_directory_id || !is_array($config)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Validate access to target company
$stmt = $pdo->prepare("
    SELECT c.id
    FROM companies c
    JOIN user_companies uc ON uc.company_id = c.id
    WHERE uc.user_id = ? AND c.directory_id = ?
    LIMIT 1
");
$stmt->execute([$user_id, $target_directory_id]);
$company = $stmt->fetch();

if (!$company) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access to company']);
    exit;
}

// Save config
$configPath = dirname(__DIR__) . "/config/$target_directory_id";
$jsonFile = $configPath . "/applications.json";

if (!is_dir($configPath)) {
    mkdir($configPath, 0755, true);
}

$result = file_put_contents($jsonFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to write config']);
    exit;
}

echo json_encode(['status' => 'success', 'directoryId' => $target_directory_id]);