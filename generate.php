<?php
require 'assets/auth.php';
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Access denied. Please log in.");
}

// Step 1: Fetch companies linked via user_companies
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.directory_id
    FROM companies c
    JOIN user_companies uc ON uc.company_id = c.id
    WHERE uc.user_id = ?
");
$stmt->execute([$user_id]);
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Step 2: Fetch user's primary company from users table
$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.directory_id
    FROM companies c
    JOIN users u ON u.company_id = c.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$primaryCompany = $stmt->fetch(PDO::FETCH_ASSOC);

// Step 3: Merge without duplicates
$companyMap = [];
foreach ($companies as $c) {
    $companyMap[$c['id']] = $c;
}
if ($primaryCompany && !isset($companyMap[$primaryCompany['id']])) {
    $companyMap[$primaryCompany['id']] = $primaryCompany;
}
$companies = array_values($companyMap);

if (empty($companies)) {
    die("No companies linked to your account.");
}

// Step 4: Determine selected company
$directory_id = $_GET['DS'] ?? null;
$company = null;
foreach ($companies as $c) {
    if ($c['directory_id'] === $directory_id) {
        $company = $c;
        break;
    }
}
if (!$company) {
    $company = $companies[0];
    $directory_id = $company['directory_id'];
}
$company_id = $company['id'];
$_SESSION['company_id'] = $company_id;

// Step 5: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_key = trim($_POST['product_key'] ?? '');
    $admin_username = trim($_POST['admin_username'] ?? '');
    $admin_password = trim($_POST['admin_password'] ?? '');
    $wifi_ssid = trim($_POST['wifi_ssid'] ?? '');
    $wifi_password = trim($_POST['wifi_password'] ?? '');

    $valid_pattern = '/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/';
    if (!preg_match($valid_pattern, $product_key)) {
        $product_key = '00000-00000-00000-00000-00000';
    }

    if ($admin_username === '' || $admin_password === '') {
        die("Admin username and password are required.");
    }

    if ($wifi_ssid === '' || $wifi_password === '') {
        die("Wi-Fi SSID and password are required.");
    }

    function obfuscate_password($plain) {
        $raw = $plain . "Password";
        return base64_encode(mb_convert_encoding($raw, 'UTF-16LE'));
    }

    function ssid_to_hex($ssid) {
        return strtoupper(bin2hex($ssid));
    }

    $obfuscated_password = obfuscate_password($admin_password);

    $wlan_profile = htmlspecialchars(
        '<WLANProfile xmlns="http://www.microsoft.com/networking/WLAN/profile/v1">
            <name>' . $wifi_ssid . '</name>
            <SSIDConfig>
                <SSID>
                    <hex>' . ssid_to_hex($wifi_ssid) . '</hex>
                    <name>' . $wifi_ssid . '</name>
                </SSID>
            </SSIDConfig>
            <connectionType>ESS</connectionType>
            <connectionMode>auto</connectionMode>
            <MSM>
                <security>
                    <authEncryption>
                        <authentication>WPA2PSK</authentication>
                        <encryption>AES</encryption>
                        <useOneX>false</useOneX>
                    </authEncryption>
                    <sharedKey>
                        <keyType>passPhrase</keyType>
                        <protected>false</protected>
                        <keyMaterial>' . $wifi_password . '</keyMaterial>
                    </sharedKey>
                </security>
            </MSM>
        </WLANProfile>',
        ENT_NOQUOTES
    );

    $template_path = __DIR__ . '/templates/autounattend.template.xml';
    if (!file_exists($template_path)) {
        die("Template file not found.");
    }

    $template = file_get_contents($template_path);
    $modified_xml = str_replace(
        ['Cheeseburger', 'Toothbrush', '00000-00000-00000-00000-00000', '{{ADMIN_USERNAME}}', '{{ADMIN_PASSWORD}}', '{{WLAN_PROFILE}}', '{{WIFI_SSID}}'],
        [$directory_id, DEPLOYSMART_BASE_URL, $product_key, $admin_username, $obfuscated_password, $wlan_profile, $wifi_ssid],
        $template
    );

    $workingDir = dirname(__DIR__) . '/tmp';
    $sessionDir = $workingDir . '/' . session_id();
    if (!is_dir($sessionDir)) {
        mkdir($sessionDir, 0700, true);
        chown($sessionDir, 'mspot-deploysmart-dev');
    }

    $xmlPath = $sessionDir . '/autounattend.xml';
    $isoPath = $sessionDir . '/autounattend.iso';

    file_put_contents($xmlPath, $modified_xml);

    if (isset($_POST['generate_xml'])) {
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="autounattend.xml"');
        ob_clean();
        echo $modified_xml;
        unlink($xmlPath);
        rmdir($sessionDir);
        exit;
    }

    if (isset($_POST['generate_iso'])) {
        $cmd = "genisoimage -o " . escapeshellarg($isoPath) . " -V DeploySmart -J -r " . escapeshellarg($sessionDir);
        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0 || !file_exists($isoPath)) {
            die("ISO generation failed.");
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="autounattend.iso"');
        ob_clean();
        flush();
        readfile($isoPath);

        register_shutdown_function(function () use ($xmlPath, $isoPath, $sessionDir) {
            @unlink($xmlPath);
            @unlink($isoPath);
            @rmdir($sessionDir);
        });
        exit;
    }
}

header('Content-Type: text/html; charset=utf-8');
include('./assets/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DeploySmart - Generate Deployment Files</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="content">
        <h2>Generate Windows Deployment Files</h2>
        <p>
            Choose the format that fits your deployment method:
            <ul style="margin-left: 20px;">
                <li><strong>XML only</strong> – Use this if you want to manually edit or embed the autounattend.xml into an existing ISO or USB stick.</li>
                <li><strong>ISO file</strong> – Use this if you want a ready-to-boot ISO with your autounattend.xml already included. Ideal for VM testing or automated USB installs.</li>
            </ul>
        </p>

        <form method="POST">
            <?php if (!isset($_GET['DS'])): ?>
                <label for="company_id">Select Company:</label><br>
                <select id="comp-sel" name="company_id" onchange="const ds = this.options[this.selectedIndex].getAttribute('data-ds'); if (ds) location.href='?DS=' + ds;">
				    <option>-- Select a company --</option>
				    <?php foreach ($companies as $c): ?>
			        <option data-ds="<?= htmlspecialchars($c['directory_id']) ?>">
			            <?= htmlspecialchars($c['name']) ?>
			        </option>
				    <?php endforeach; ?>
				</select><br><br>
            <?php endif; ?>

            <!-- Windows (VL) License settings -->
            <label for="product_key">Windows (VL) License Key:</label><br>
            <input type="text" id="product_key" name="product_key" placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX"
                pattern="^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$"
                title="Enter a valid 25-character license key"
            ><br>
            <small style="color: #999;">
                Use your organization's Volume License key. If unsure, leave blank to use a placeholder key.
            </small><br><br>

            <!-- Local admin settings -->
            <label for="admin_username">Local Admin Username:</label><br>
            <input type="text" id="admin_username" name="admin_username" placeholder="AdminUser" required><br><br>

            <label for="admin_password">Local Admin Password:</label><br>
            <input type="password" id="admin_password" name="admin_password" placeholder="SuperSecret123" required><br><br>

            <!-- Wireless profile settings -->
            <label for="wifi_ssid">Wi-Fi SSID:</label><br>
            <input type="text" id="wifi_ssid" name="wifi_ssid" placeholder="SSID" required><br><br>

            <label for="wifi_password">Wi-Fi Password:</label><br>
            <input type="text" id="wifi_password" name="wifi_password" placeholder="Password" required><br><br>

            <div class="actions">
                <button type="submit" name="generate_xml" class="btn-add">📄 Download autounattend.xml</button>
                <button type="submit" name="generate_iso" class="btn-add">📥 Download autounattend.iso</button>
            </div>
        </form>
    </div>

    <?php include('./assets/footer.php'); ?>
</body>
</html>
