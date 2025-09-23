# Script made by Mattias Magnusson 2025-05-20
# Dynamically installs applications from a JSON list hosted on deploysmart.dev.mspot.se
# Updated 2025-09-22

Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing
$tp = "C:\TempPath"
$tl = "C:\logs"
$ID = "{DEPLOYMENT_ID}"
$DOMAIN = "Toothbrush"

# Installer Form
$formInstall = New-Object System.Windows.Forms.Form
$formInstall.Text = "DeploySmart Installer"
$formInstall.Size = New-Object System.Drawing.Size(510, 410)
$formInstall.StartPosition = "CenterScreen"
$formInstall.FormBorderStyle = 'FixedDialog'
$formInstall.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#F3F3F3")
$formInstall.Font = New-Object System.Drawing.Font("Segoe UI", 10, [System.Drawing.FontStyle]::Regular)
$formInstall.TopMost = $true
$formInstall.ControlBox = $false

# Load favicon
$iconPath = "C:\Windows\Setup\DeploySmart.ico"
if (-not (Test-Path $iconPath)) {
    try {
        Invoke-WebRequest -Uri "https://deploysmart.dev.mspot.se/favicon.ico" -OutFile $iconPath -UseBasicParsing
    } catch {
        Write-Warning "Could not download favicon."
    }
}
try {
    $iconBitmap = [System.Drawing.Bitmap]::FromFile($iconPath)
    $formInstall.Icon = [System.Drawing.Icon]::FromHandle($iconBitmap.GetHicon())
} catch {
    $formInstall.Icon = [System.Drawing.Icon]::ExtractAssociatedIcon("C:\Windows\System32\shell32.dll")
}

# Info Label
$labelInfo = New-Object System.Windows.Forms.Label
$labelInfo.Size = New-Object System.Drawing.Size(460, 50)
$labelInfo.Location = New-Object System.Drawing.Point(20, 20)
$labelInfo.Text = "Installing applications. Please do not turn off your computer or close this window."
$labelInfo.TextAlign = 'TopLeft'
$formInstall.Controls.Add($labelInfo)

# Status ListBox
$listBox = New-Object System.Windows.Forms.ListBox
$listBox.Size = New-Object System.Drawing.Size(460, 280)
$listBox.Location = New-Object System.Drawing.Point(20, 80)
$listBox.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#FFFFFF")
$listBox.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#333333")
$formInstall.Controls.Add($listBox)

$formInstall.Show()

# Load application list
$jsonUrl = "$DOMAIN/config/$ID/applications.json"
$logPath = "C:\InstallLogs\install_log.txt"
New-Item -Path $logPath -ItemType File -Force | Out-Null

try {
    $applications = Invoke-RestMethod -Uri $jsonUrl -UseBasicParsing
} catch {
    [System.Windows.Forms.MessageBox]::Show("Failed to load application list.", "Error", 'OK', 'Error')
    exit 1
}

foreach ($app in $applications) {
    $listBox.Items.Add("⏳ Waiting: $($app.Name)")
    Add-Content -Path $logPath -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - ⏳ Waiting: $($app.Name)"
    [System.Windows.Forms.Application]::DoEvents()
}

$failedApps = @()

# Install loop
for ($i = 0; $i -lt $applications.Count; $i++) {
    $app = $applications[$i]
    $listBox.Items[$i] = "🔧 Installing: $($app.Name)"
    [System.Windows.Forms.Application]::DoEvents()
    try {
        irm $app.Url | iex
        $listBox.Items[$i] = "✅ Installed: $($app.Name)"
        Add-Content -Path $logPath -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - ✅ Installed: $($app.Name)"
    } catch {
        $listBox.Items[$i] = "❌ Failed: $($app.Name)"
        $errorMessage = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Failed to install $($app.Name): $($_.Exception.Message)"
        Add-Content -Path $logPath -Value $errorMessage
        $failedApps += $app
    }
    Start-Sleep -Seconds 1
    [System.Windows.Forms.Application]::DoEvents()
}

# Retry loop
if ($failedApps.Count -gt 0) {
    Add-Content -Path $logPath -Value "`n--- RETRYING FAILED INSTALLATIONS ---`n"
    foreach ($app in $failedApps) {
        $index = $applications.IndexOf($app)
        $listBox.Items[$index] = "🔁 Retrying: $($app.Name)"
        [System.Windows.Forms.Application]::DoEvents()
        try {
            irm $app.Url | iex
            $listBox.Items[$index] = "✅ Installed on Retry: $($app.Name)"
            Add-Content -Path $logPath -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - ✅ Installed on Retry: $($app.Name)"
        } catch {
            $listBox.Items[$index] = "❌ Still Failed: $($app.Name)"
            $errorMessage = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Retry failed for $($app.Name): $($_.Exception.Message)"
            Add-Content -Path $logPath -Value $errorMessage
        }
        Start-Sleep -Seconds 1
        [System.Windows.Forms.Application]::DoEvents()
    }
}

Start-Sleep -Seconds 5
$formInstall.Close()
