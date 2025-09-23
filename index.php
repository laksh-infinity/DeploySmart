<?php
require 'db.php';
session_start();
include './assets/header.php';
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<div class="hero">
    <div class="hero-overlay">
        <h1><span class="deploy">Deploy</span><span class="smart">Smart</span></h1>
        <p>Automated Windows deployment with precision, speed, and security.</p>
        <a href="how-to.php">Get Started</a>
    </div>
</div>

<div class="section">
    <h2>What is DeploySmart?</h2>
    <p>
        DeploySmart is a powerful deployment tool designed to streamline Windows setup across devices. 
        It automates installation of pre-defined applications, configures local admin accounts, adds Wi-Fi profiles, 
        runs Windows Updates, and even joins Active Directory—all through secure PowerShell scripts and service accounts.
    </p>
</div>

<div class="section">
    <h2>Why Choose DeploySmart?</h2>
    <p>
        Unlike other deployment solutions, DeploySmart offers:
        <ul>
            <li><strong>Cost Efficiency:</strong> No bloated licensing fees—just streamlined functionality.</li>
            <li><strong>Privacy First:</strong> No logs, no tracking. Your data stays yours.</li>
            <li><strong>Real-Time Configuration:</strong> Modify deployment parameters on the fly.</li>
            <li><strong>Version Control:</strong> Ensure the latest apps and updates are always installed.</li>
        </ul>
    </p>
</div>

<div class="services-section">
    <h2>Our Services</h2>
    <div class="cards">
        <div class="card">
            <h3>🪟 Automated Windows Setup</h3>
            <p>Windows is installed using an autounattended configuration file, enabling a fully hands-free setup process. You can now customize your own autounattended.xml that never touches our servers!</p>
        </div>
        <div class="card">
            <h3>🔐 Admin Account Setup</h3>
            <p>Preconfigure a local administrator account securely during deployment, let the setup run it'self.</p>
        </div>
        <div class="card">
            <h3>📦 Application Deployment</h3>
            <p>With DeploySmart you select a pre-defined set of applications to be installed during first boot using our dashboard for autonomous PowerShell automation.</p>
        </div>
        <div class="card">
            <h3>📶 Wi-Fi Profile Injection</h3>
            <p>Push Wi-Fi profiles and credentials during Windows setup for instant connectivity when Windows boots (Some devices may need etherneth).</p>
        </div>
        <div class="card">
            <h3>🔄 Windows Update</h3>
            <p>Let DeploySmart pre-install Windows updates automatically; DeploySmart ensures they run immediately after application setup is done.</p>
        </div>
        <div class="card">
            <h3>🏢 Active Directory Join</h3>
            <p>Join devices to AD using service account credentials—fully automated via PowerShell.</p>
        </div>
        <div class="card">
            <h3>🧩 Push your own applications</h3>
            <p>Now you can include your own PowerShell scripts to run durring install, locked down under your company profile.</p>
        </div>
        <div class="card">
            <h3>🖥️ Ready To Use</h3>
            <p>When the setup is done, the computer is ready for your users, everything is already installed and setup.</p>
        </div>
    </div>
</div>
<div class="news-section">
	<h2>What's new?</h2>
	<?php include('content/news.txt'); ?>
</div>
<div class="news-section">
	<h2>What's planned?</h2>
	<?php include('content/plans.txt'); ?>
</div>

<div class="news-section">
	<h2>Rant!?</h2>
	<?php include('content/rant.txt'); ?>
</div>
<?php include './assets/footer.php'; ?>