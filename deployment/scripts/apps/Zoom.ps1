# Automate the install of Zoom.
# Script made by Mattias Magnusson 2025-06-09
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
mkdir $tp
$Url = "https://zoom.com/client/latest/ZoomInstallerFull.msi?archType=x64"
$Installer = "ZoomInstallerFull.msi"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer
Start-Process -FilePath $tp\$Installer -ArgumentList "/qn" -Wait
Start-Sleep -Seconds 10
del $tp\$Installer