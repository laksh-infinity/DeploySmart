# Automate the install of Floorp Browser.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$owner = "Floorp-Projects"
$repo = "Floorp"
$Installer = "floorp-win64.installer.exe"
$latestRelease = Invoke-RestMethod -Uri "https://api.github.com/repos/$owner/$repo/releases/latest"
$assetUrl = $latestRelease.assets | Where-Object { $_.name -eq "$Installer" } | Select-Object -ExpandProperty browser_download_url
Invoke-WebRequest -Uri $assetUrl -OutFile "$tp\$Installer"
Start-Process -FilePath $tp\$Installer -ArgumentList "/S /PreventRebootRequired=true" -Wait
Start-Sleep -Seconds 30
del $tp\$Installer
