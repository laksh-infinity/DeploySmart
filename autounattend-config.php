<?php
require 'assets/auth.php';
include('db.php');
include('./assets/header.php');
header('Content-Type: text/html; charset=utf-8');

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
    $deployment_id = $directory_id;

    $config_path = "./config/$directory_id/autounattend.xml";
    $template_path = "./template/autounattend.template.xml";

    // Load XML
    $xml = simplexml_load_file($config_path);
    if (!$xml) {
        die("Failed to load XML from $config_path");
    }

    // Handle Save
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        // Safe XPath assignments
        if ($keyNode = $xml->xpath('//Key')) {
            $keyNode[0][0] = $_POST['product_key'];
        }
        if ($adminPassNode = $xml->xpath('//UserAccounts/AdministratorPassword/Value')) {
            $adminPassNode[0][0] = base64_encode($_POST['admin_password']);
        }
        if ($adminUserNode = $xml->xpath('//UserAccounts/LocalAccounts/LocalAccount/Name')) {
            $adminUserNode[0][0] = $_POST['admin_username'];
        }
        if ($ssidNode = $xml->xpath('//SSID')) {
            $ssidNode[0][0] = $_POST['wifi_ssid'];
        }
        if ($wifiPassNode = $xml->xpath('//KeyMaterial')) {
            $wifiPassNode[0][0] = $_POST['wifi_password'];
        }

        // Inject deployment ID
        if ($envNode = $xml->xpath('//Environment')) {
            $envNode[0]->ID = $deployment_id;
        }

        $xml->asXML($config_path);
        echo "<div style='color: green;'>Saved successfully!</div>";
    }

    // Handle Reset
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
        if (copy($template_path, $config_path)) {
            echo "<div style='color: orange;'>Reset to template!</div>";
        } else {
            echo "<div style='color: red;'>Failed to reset file.</div>";
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<form method="post">
    Product Key: <input name="product_key" value="<?= htmlspecialchars($xml->xpath('//Key')[0] ?? '') ?>"><br>
    Admin Username: <input name="admin_username" value="<?= htmlspecialchars($xml->xpath('//UserAccounts/LocalAccounts/LocalAccount/Name')[0] ?? '') ?>"><br>
    Admin Password: <input name="admin_password"><br>
    Wi-Fi SSID: <input name="wifi_ssid" value="<?= htmlspecialchars($xml->xpath('//SSID')[0] ?? '') ?>"><br>
    Wi-Fi Password: <input name="wifi_password" value="<?= htmlspecialchars($xml->xpath('//KeyMaterial')[0] ?? '') ?>"><br>
    <button name="save">Save</button>
    <button name="reset">Reset</button>
</form>

<?php include('./assets/footer.php'); ?>