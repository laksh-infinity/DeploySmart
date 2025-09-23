# Automate the install and scan of ClamAV.
# Script made by Mattias Magnusson 2025-08-26
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
# Set working directory
$ClamAVDir = "C:\ClamAV\"
$ClamAVDirtwo = "C:\ClamAV\clamav-1.4.3.win.x64"
$LogFile = "$ClamAVDir\scan-log.txt"

# Create directory if it doesn't exist
if (!(Test-Path $ClamAVDir)) {
    New-Item -ItemType Directory -Path $ClamAVDir
}

# Download ClamAV for Windows
$InstallerUrl = "https://www.clamav.net/downloads/production/clamav-1.4.3.win.x64.zip"
$ZipPath = "$ClamAVDir\clamav.zip"

Invoke-WebRequest -Uri $InstallerUrl -OutFile $ZipPath

# Extract the zip file
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::ExtractToDirectory($ZipPath, $ClamAVDir)

# Configure ClamAV
#Copy-Item "$ClamAVDir\conf_examples\freshclam.conf.sample" "$ClamAVDir\freshclam.conf"
Invoke-WebRequest -Uri https://deploysmart.dev.mspot.se/deployment/files/freshclam.conf -OutFile "$ClamAVDirtwo\freshclam.conf"
#mkdir "C:\Program Files\ClamAV\"

# Update virus definitions
Start-Process -FilePath "$ClamAVDirtwo\freshclam.exe" -Wait

# Run full system scan
Start-Process -FilePath "$ClamAVDirtwo\clamscan.exe" -ArgumentList "--recursive --infected C:\", "--log=$LogFile" -Wait
