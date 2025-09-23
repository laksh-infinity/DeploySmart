# Dynamically installs applications from a JSON list hosted online
# Script made by Mattias Magnusson 2025-06-05

$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$tp = "C:\TempPath"
if (-not (Test-Path $tp)) {
    New-Item -ItemType Directory -Path $tp | Out-Null
}

# Step 1: Get the latest filename from the directory listing
$html = Invoke-WebRequest -Uri "https://get.videolan.org/vlc/last/win64/" -UseBasicParsing
$fileName = ($html.Content | Select-String -Pattern "vlc-[\d\.]+-win64\.exe" | Select-Object -First 1).Matches.Value

if (-not $fileName) {
    Write-Host "❌ Could not find VLC installer filename."
    exit 1
}

# Step 2: Extract version from filename
$version = ($fileName -split "-")[1]

# Step 3: Construct full mirror URL
$mirrorBase = "https://ftp.lysator.liu.se/pub/videolan/vlc"
$installerUrl = "$mirrorBase/$version/win64/$fileName"
$installerPath = Join-Path $tp $fileName

# Write-Host "Downloading: $installerUrl"

# Step 4: Download the installer
Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath

# Step 5: Wait until the file is fully written
$timeout = 60
$sw = [Diagnostics.Stopwatch]::StartNew()
while (-not (Test-Path $installerPath) -or (Get-Item $installerPath).Length -lt 1000000) {
    Start-Sleep -Milliseconds 500
    if ($sw.Elapsed.TotalSeconds -gt $timeout) {
        Write-Host "❌ Download timed out or file incomplete."
        exit 1
    }
}

Start-Process -FilePath $installerPath -ArgumentList "/L=1033 /S /NCRC /quiet /norestart" -Wait

Start-Sleep -Seconds 5
Remove-Item $installerPath -Force

###						###
#  This part is disabled! #
### 					###

#$ProgressPreference = 'SilentlyContinue'
#[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
#$tp = "C:\TempPath"
#if (-not (Test-Path $tp)) {
#    New-Item -ItemType Directory -Path $tp | Out-Null
#}
#$html = Invoke-WebRequest -Uri "https://get.videolan.org/vlc/last/win64/" -UseBasicParsing
#$fileName = ($html.Content | Select-String -Pattern "vlc-[\d.]+-win64\.exe" | Select-Object -First 1).Matches.Value
#
#$installerUrl = "https://get.videolan.org/vlc/last/win64/$fileName"
#$installerPath = Join-Path $tp $fileName
#Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath
#
#Start-Sleep -Seconds 60
#
#if (-not $installerPath) {
#    Write-Host "❌ Could not find VLC installer filename."
#    exit 1
#}
#
#Start-Process -FilePath $installerPath -ArgumentList "/L=1033 /S /NCRC /quiet /norestart" -Wait
#
#Start-Sleep -Seconds 5
#Remove-Item $installerPath -Force
#