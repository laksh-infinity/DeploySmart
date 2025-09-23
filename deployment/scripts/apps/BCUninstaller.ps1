# Automate the install of Bull Crap Unintaller.
# Script made by Mattias Magnusson 2025-05-20
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
$owner = "Klocman"
$repo = "Bulk-Crap-Uninstaller"
$latestRelease = Invoke-RestMethod -Uri "https://api.github.com/repos/$owner/$repo/releases/latest"
$asset = $latestRelease.assets | Where-Object { $_.name -like "BCUninstaller_*_setup.exe" } | Select-Object -First 1
$assetUrl = $asset.browser_download_url
$installerPath = "$tp\$($asset.name)"
Invoke-WebRequest -Uri $assetUrl -OutFile $installerPath
Start-Process -FilePath $installerPath -ArgumentList "/silent /suppressmsgboxes /norestart" -Wait
Start-Sleep -Seconds 10
del $installerPath