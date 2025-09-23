<?php
#require 'db.php';
require_once(__DIR__ . '/../db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Refresh apps in both company space and global space.																					
#include('./cron.php');

// Fetch logged-in user's company directory_id
$directory_id = 'Unknown'; // Default fallback

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT companies.directory_id FROM users JOIN companies ON users.company_id = companies.id WHERE users.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    if ($result && isset($result['directory_id'])) {
        $directory_id = $result['directory_id'];
    }
}

$user = null;

if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, surname, is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}


?>
<head>
    <meta charset="UTF-8">
    <!-- <title>DeploySmart - Application Manager</title> -->
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
		<div class="logo">
        	<h1><span class="deploy">Deploy</span><span class="smart">Smart</span></h1>
        	<h2>Application Manager</h2>
      	</div>
      	<div class="menu">
			<?php include(__DIR__ . '/menu.php'); ?>
		</div>
        <?php if (isset($_SESSION['user_id'])): ?>
			<div class="sub-menu">
				<p>Deployment ID: <?= htmlspecialchars($directory_id ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?> | <a href="./generate.php">📥 Generate autounattend.xml</a>
</p>
			</div>
		<?php endif; ?>

    </header>
