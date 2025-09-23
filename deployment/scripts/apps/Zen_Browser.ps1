# Automate the install of Zen Browser.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$owner = "zen-browser"
$repo = "desktop"
$latestRelease = Invoke-RestMethod -Uri "https://api.github.com/repos/$owner/$repo/releases/latest"
$asset = $latestRelease.assets | Where-Object { $_.name -like "zen.installer.exe" } | Select-Object -First 1
$assetUrl = $asset.browser_download_url
$installerPath = "$tp\$($asset.name)"
Invoke-WebRequest -Uri $assetUrl -OutFile $installerPath
Start-Process -FilePath $installerPath -ArgumentList "/s" -Wait
Start-Sleep -Seconds 30
del $installerPath
