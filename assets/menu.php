<nav class="main-menu">
    <div class="menu-toggle" onclick="document.querySelector('.main-menu ul').classList.toggle('open')">
        ☰
    </div>
    <ul>
        <li><a href="/index.php">🏠 Home</a></li>

        <?php if ($user): ?>
            <li><a href="/dashboard.php">🛠️ Dashboard</a></li>
            <li><a href="/configure2.php">⚙️ Configure Applications</a></li>
            <li><a href="/how-to.php">📘 How to</a></li>
            <li class="user-menu">
                <a href="#">👤 <?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?> ▼</a>
                <ul class="dropdown">
                    <li><a href="/profile.php">Profile Settings</a></li>
                    <li><a href="/company_settings.php">Company Settings</a></li>
                    <li><a href="/manage.php">Managed Companies</a></li>
                    <?php if ($user['is_admin']): ?>
                        <li><a href="/admin/dashboard.php">Admin Dashboard</a></li>
                        <li><a href="/admin/users.php">Manage Users</a></li>
                        <li><a href="/admin/companies.php">Manage Companies</a></li>
                    <?php endif; ?>
                    <li><a href="/logout.php">Logout</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li><a href="#/Buy.php">⚙️ Get Applications Manager</a></li>
            <li><a href="/how-to.php">📘 How to</a></li>
            <li><a href="/login.php">🔐 Login</a></li>
        <?php endif; ?>
    </ul>
</nav>