# Automate the install of OpenShot.
# Script made by Mattias Magnusson 2025-06-09
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
mkdir $tp -ErrorAction SilentlyContinue
$owner = "OpenShot"
$repo = "openshot-qt"
$latestRelease = Invoke-RestMethod -Uri "https://api.github.com/repos/$owner/$repo/releases/latest"
$asset = $latestRelease.assets | Where-Object { $_.name -like "*x86_64.exe" } | Select-Object -First 1
$assetUrl = $asset.browser_download_url
$installerPath = "$tp\$($asset.name)"
Invoke-WebRequest -Uri $assetUrl -OutFile $installerPath
Start-Process -FilePath $installerPath -ArgumentList "/verysilent /suppressmsgboxes /norestart /restartapplications /allusers" -Wait
Start-Sleep -Seconds 10
Remove-Item $installerPath -Force