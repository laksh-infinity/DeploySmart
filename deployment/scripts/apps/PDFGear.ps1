# Automate the install of PDFGear.
# Script made by Mattias Magnusson 2025-08-15
#Updated 2025-09-20
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$tp = "C:\TempPath"
if (-not (Test-Path $tp)) {
    New-Item -ItemType Directory -Path $tp | Out-Null
}

# Fetch raw HTML using curl-style method to avoid IE engine
try {
    $rawHtml = (Invoke-WebRequest -Uri "https://www.pdfgear.com/download/" -UseBasicParsing).Content
} catch {
    Write-Host "Failed to fetch HTML content: $_"
    exit 1
}

# Extract download URL using regex
$downloadUrl = $null
if ($rawHtml -match 'https://downloadfiles\.pdfgear\.com/releases/windows/[^"]+\.exe') {
    $downloadUrl = $matches[0]
}


if (-not $downloadUrl) {
    Write-Host "Download URL not found. Exiting."
    exit 1
}

# Download installer with retry logic
$installerPath = "$tp\PDFGearInstaller.exe"
$maxRetries = 3
$attempt = 0
$success = $false

do {
    try {
        Invoke-WebRequest -Uri $downloadUrl -OutFile $installerPath
        $success = $true
    } catch {
        $attempt++
        Start-Sleep -Seconds 5
    }
} until ($success -or $attempt -ge $maxRetries)

if (-not $success) {
    Write-Host "Failed to download installer after $maxRetries attempts."
    exit 1
}

# Run installer silently
try {
    Start-Process -FilePath $installerPath -ArgumentList "/verysilent /suppressmsgboxes /norestart /SP-" -Wait
    Start-Sleep -Seconds 10
    Remove-Item $installerPath -Force
    Write-Host "PDFGear installed successfully."
} catch {
    Write-Host "Installer failed to run: $_"
}