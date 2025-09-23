# Automate the install of Arc Browser.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$Installer = "ArcInstaller.exe"
$Url = "https://releases.arc.net/windows/$Installer"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer
Start-Process -FilePath $tp\$Installer -ArgumentList "/silent /verysilent /quiet /q /s" -Wait
Start-Sleep -Seconds 30
del $tp\$Installer
