<?php
require '../db.php';
require '../assets/auth.php'; // ensures session is active

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        echo "<pre>Fatal error: " . print_r($error, true) . "</pre>";
    }
});


// ✅ Admin check
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to register users.</p></div>';
    exit;
}

$company_id = $_GET['company_id'] ?? null;

if (!$company_id || !is_numeric($company_id)) {
    echo '<div class="content"><h2>Missing Company ID</h2></div>';
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($email) || empty($password) || empty($name) || empty($surname)) {
        $message = "❌ All fields except phone are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $message = "❌ A user with this email already exists.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, surname, phone, company_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$email, $password_hash, $name, $surname, $phone, $company_id]);

                // ✅ Redirect after success
                header("Location: ./dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $message = "❌ Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<?php include '../assets/header.php'; ?>
<div class="content">
    <h2>Register User for Company ID <?= htmlspecialchars($company_id) ?></h2>

    <?php if ($message): ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>First Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="surname" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone"><br><br>

        <input type="submit" value="Register User">
    </form>
</div>
<?php include '../assets/footer.php'; ?>