<?php
require '../db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get logged-in user's company directory ID
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$stmt = $pdo->prepare("SELECT company_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$stmt = $pdo->prepare("SELECT directory_id FROM companies WHERE id = ?");
$stmt->execute([$user['company_id']]);
$company = $stmt->fetch();

$directory_id = $company['directory_id'] ?? null;

if (!$directory_id) {
    http_response_code(404);
    echo json_encode(['error' => 'Company directory not found']);
    exit;
}

// Prepare path
$configPath = dirname(__DIR__) . "/config/$directory_id";
$jsonFile = $configPath . "/applications.json";

// Create directory if needed
if (!is_dir($configPath)) {
    mkdir($configPath, 0755, true);
}

// Check directory before saving
if (!$directory_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing directory ID']);
    exit;
}

// Save incoming JSON
$data = file_get_contents("php://input");
file_put_contents($jsonFile, $data);


if (file_put_contents($jsonFile, $data) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to write to file']);
    exit;
}


echo json_encode(['status' => 'success']);
echo ($directory_id);
?>
