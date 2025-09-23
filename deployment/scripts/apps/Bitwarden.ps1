# Automate the install of Bitwarden Password Manager.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$installerUrl = "https://bitwarden.com/download/?app=desktop&platform=windows&variant=exe"
$installerPath = "$tp\BitwardenInstaller.exe"
Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath
Start-Process -FilePath $installerPath -ArgumentList "/allusers /S" -Wait
Start-Sleep -Seconds 10
del $installerPath
