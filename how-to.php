<?php
require 'db.php';
include('./assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Safely get company_id from session
$company_id = $_SESSION['company_id'] ?? null;
$company_name = null;

if ($company_id) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $company = $stmt->fetch();

        if ($company) {
            $company_name = $company['name'];
        }
    } catch (PDOException $e) {
        // Optional: log error or show fallback
        error_log("Database error: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>DeploySmart - How-To</title>
  <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
  <section class="section">
    <h2>🧩 How DeploySmart Works</h2>
    <p>DeploySmart simplifies Windows deployment across your organization—whether you're setting up new devices or configuring existing ones. Here's how it works:</p>

    <h3>🪟 1. Bring Your Own Windows Image</h3>
    <p>You start with your own Windows installation media:</p>
    <ul>
      <li>USB stick</li>
      <li>PXE boot server</li>
      <li>ISO file</li>
    </ul>
    <p>DeploySmart doesn’t replace your image—it enhances it with automation and consistency.</p>

    <h3>⚙️ 2. Autounattended Configuration</h3>
    <p>We provide a streamlined <code>autounattended.xml</code> file that automates the Windows setup process. You can easily reconfigure it to include:</p>
    <ul>
      <li>✅ Your Windows license key</li>
      <li>👤 Local admin username and password (encrypted in base64 for security)</li>
      <li>📶 Wi-Fi credentials (encrypted in base64 for security)</li>
    </ul>
    <p>This ensures a hands-free, consistent deployment across all devices.</p>

    <h3>🛠️ 3. PowerShell Runtime Options</h3>
    <p>DeploySmart offers two types of PowerShell runtimes to handle post-install configuration:</p>
	<p>Use our tool to modify your own applications.json to include what ever applications you need from our list of availiable applications.</p>
	<p>If any application is missing, feel free to contact us and we will resolve the automation for you.</p>

    <h3>🔹 Embedded Runtime (New Deployments)</h3>
    <p>Included directly in the <code>autounattended.xml</code>, this script runs during first boot and:</p>
    <ul>
      <li>Installs your pre-defined applications</li>
      <li>Applies system tweaks</li>
      <li>Connects to your company-specific configuration</li>
    </ul>

    <h4>🔹 Remote Runtime (Existing Devices)</h4>
    <p>Already have devices deployed? No problem. Run this command on any Windows machine:</p>
    <pre><code>irm http://deploysmart.dev.mspot.se/deploy.php?DS={DeploySmart_ID} | iex</code></pre>
    <p>This allows you to apply the same configuration and app setup to machines already in use—without reimaging.</p>

    <h3>🔄 4. Cross-Device Consistency</h3>
    <p>Whether you're deploying fresh systems or updating existing ones, DeploySmart ensures:</p>
    <ul>
      <li>🧩 The same apps and settings are applied</li>
      <li>🔄 Updates are consistent</li>
      <li>🚀 Devices are ready for production faster</li>
    </ul>

    <h3>🖼️ Optional Visuals</h3>
		<div class="custom-wizard">
			<div class="wizard-step active">
				<span>USB / PXE</span>
			</div>
			<div class="wizard-step two">
				<span>Autounattended</span>
			</div>
			<div class="wizard-step three">
				<span>PowerShell</span>
			</div>
			<div class="wizard-step final">
				<span>Configured Device</span>
			</div>
		</div>
  </section>
</body>
<?php include('./assets/footer.php'); ?>
</html>