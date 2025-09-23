# Automate the install of Brave Browser.
# Script made by Mattias Magnusson 2025-08-22
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$Url = "https://brave-browser-downloads.s3.brave.com/latest/brave_installer-x64.exe"
$Installer = "brave_installer-x64.exe"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer
Start-Process -FilePath $tp\$Installer -ArgumentList "--install --silent --system-level" -Wait
Start-Sleep -Seconds 30
del $tp\$Installer