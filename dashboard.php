<?php
require 'assets/auth.php';
#session_start();
include ('./assets/header.php');
include('./db.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['company_id'])) {
    die("Access denied. Please log in.");
}

$company_id = $_SESSION['company_id'];

try {
    // Fetch company info
    $stmt = $pdo->prepare("SELECT name, directory_id FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch();

    if (!$company) {
        die("Company not found.");
    }

    $company_name = $company['name'];
    $directory_id = $company['directory_id'];
    $json_path = __DIR__ . "/$directory_id/prod/applications.json";

    // Load applications.json
    $applications = [];
    if (file_exists($json_path)) {
        $json_data = file_get_contents($json_path);
        $applications = json_decode($json_data, true);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($company_name) ?> - Dashboard</title>
</head>
<body>
  <div class="content">
    <h1>Welcome to <?= htmlspecialchars($company_name) ?>'s Dashboard</h1>
    <h2>Start by configuring your applications list, dont forget to press save in the top right corner!</h2>
    <p><a href="/configure2.php">➤ Configure your Applications</a></p>
	<h2>Show applications to install (PowerShell):</h2>
    <div class="code-container">
  		<pre><code id="code-block-install">irm <?= DEPLOYSMART_BASE_URL ?>/deploy.php?ID=<?= htmlspecialchars($directory_id, ENT_QUOTES, 'UTF-8') ?></code></pre>
<button class="copy-btn" onclick="copyToClipboard('code-block-install')">📋 Copy</button>
	</div>

    <h2>Run on already deployed machine to install/update applications (PowerShell):</h2>
    <div class="code-container">
  		<pre><code id="code-block-update">irm <?= DEPLOYSMART_BASE_URL ?>/deploy.php?DS=<?= htmlspecialchars($directory_id, ENT_QUOTES, 'UTF-8') ?> | iex</code></pre>
<button class="copy-btn" onclick="copyToClipboard('code-block-update')">📋 Copy</button>


<script>
function copyToClipboard(codeId) {
    const code = document.getElementById(codeId).innerText;
    navigator.clipboard.writeText(code).then(() => {
        alert("Copied to clipboard!");
    });
}
</script>

	</div>

<p>For full/fresh deployment trough autounattended.xml, you will need to wait for us to generate that for your specific deployment.</p>
  </div>
</body>
<?php include('./assets/footer.php'); ?>
</html>
