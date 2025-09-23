<?php
require '../assets/auth.php';
include('../assets/header.php');
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to view this page.</p></div>';
    include('../assets/footer.php');
    exit;
}

// Fetch company stats
$stmt = $pdo->query("
SELECT 
    c.name,
    (SELECT COUNT(*) FROM users WHERE company_id = c.id) AS user_count,
    (SELECT COUNT(*) FROM deployments WHERE company_id = c.id) AS total_deployments,
    (SELECT COUNT(*) FROM deployments WHERE company_id = c.id AND method = 'iso') AS iso_count,
    (SELECT COUNT(*) FROM deployments WHERE company_id = c.id AND method = 'powershell') AS ps_count,
    (SELECT MAX(timestamp) FROM deployments WHERE company_id = c.id) AS last_deployment
FROM companies c
ORDER BY total_deployments DESC;
");

$companies = $stmt->fetchAll();

// Prepare chart data
$labels = [];
$deployments = [];
$users = [];
$iso_total = 0;
$ps_total = 0;

foreach ($companies as $c) {
    $labels[] = $c['name'];
    $deployments[] = $c['total_deployments'];
    $users[] = $c['user_count'];
    $iso_total += $c['iso_count'];
    $ps_total += $c['ps_count'];
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="content">
    <h2>📊 Deployment Statistics</h2>

    <!--<div class="chart-row">
        <div class="chart-block"><canvas id="deployChart"></canvas></div>
        <div class="chart-block chart-square"><canvas id="methodChart"></canvas></div>
        <div class="chart-block"><canvas id="userChart"></canvas></div>
    </div>-->

	<div class="chart-grid">
    	<div class="chart-left">
    	    <div class="chart-block"><canvas id="deployChart"></canvas></div>
        	<div class="chart-block"><canvas id="userChart"></canvas></div>
    	</div>
    	<div class="chart-right chart-square"><canvas id="methodChart"></canvas></div>
	</div>


    <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 40px; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Company</th>
                <th>Users</th>
                <th>Total Deployments</th>
                <th>AutoUnattend</th>
                <th>PowerShell</th>
                <th>Last Deployment</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($companies as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= $c['user_count'] ?></td>
                    <td><?= $c['total_deployments'] ?></td>
                    <td><?= $c['iso_count'] ?></td>
                    <td><?= $c['ps_count'] ?></td>
                    <td><?= $c['last_deployment'] ?? '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const labels = <?= json_encode($labels) ?>;
const deployments = <?= json_encode($deployments) ?>;
const users = <?= json_encode($users) ?>;

new Chart(document.getElementById('deployChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Total Deployments',
            data: deployments,
            backgroundColor: '#4e73df'
        }]
    }
});

new Chart(document.getElementById('methodChart'), {
    type: 'pie',
    data: {
        labels: ['ISO', 'PowerShell'],
        datasets: [{
            data: [<?= $iso_total ?>, <?= $ps_total ?>],
            backgroundColor: ['#1cc88a', '#36b9cc']
        }]
    }
});

new Chart(document.getElementById('userChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'User Count',
            data: users,
            backgroundColor: '#f6c23e'
        }]
    }
});
</script>
</body>
<?php include('../assets/footer.php'); ?>
</html>