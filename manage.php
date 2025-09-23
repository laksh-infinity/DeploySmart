<?php
require 'assets/auth.php';
include('assets/header.php');

$user_id = $_SESSION['user_id'];

// Fetch companies linked to user
$stmt = $pdo->prepare("
    SELECT c.* FROM companies c
    JOIN user_companies uc ON uc.company_id = c.id
    WHERE uc.user_id = ?
    ORDER BY c.name
");
$stmt->execute([$user_id]);
$companies = $stmt->fetchAll();
?>

<div class="content">
  <h2>Managed Companies</h2>

  <p>
    <a href="register_company.php" class="btn">➕ Create New Company</a>
  </p>

<?php foreach ($companies as $company): ?>
  <?php
    $script_folder = dirname(__DIR__) . "/config/" . $company['directory_id'] . "/scripts";
    $script_count = is_dir($script_folder) ? count(glob($script_folder . "/*.ps1")) : 0;
  ?>
  <div class="company-card">
    <div class="company-header">
      <h2><?= htmlspecialchars($company['name']) ?></h2>
      <span class="deployment-id">ID: <?= htmlspecialchars($company['directory_id']) ?></span>
    </div>

    <div class="company-actions">
      <a class="action-link" href="generate.php?DS=<?= htmlspecialchars($company['directory_id']) ?>">🧬 Generate autounattend.xml</a>
      <a class="action-link" href="configure_apps.php?DS=<?= htmlspecialchars($company['directory_id']) ?>">⚙️ Configure apps</a>
    </div>

    <div class="company-meta">
      <span>📜 Scripts: <strong><?= $script_count ?></strong></span>
      <?php if ($script_count > 0): ?>
        — <a class="action-link" href="view_scripts.php?company_id=<?= $company['id'] ?>">View Scripts</a>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php include('assets/footer.php'); ?>