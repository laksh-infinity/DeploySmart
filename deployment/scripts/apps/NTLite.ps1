# Automate the install of NTLite.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$Url = "https://downloads.ntlite.com/files/NTLite_setup_x64.exe"
$Installer = "NTLite_setup_x64.exe"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer
Start-Process -FilePath $tp\$Installer -ArgumentList "/SP /SILENT /NORESTART /CLOSEAPPLICATIONS" -Wait
Start-Sleep -Seconds 30
del $tp\$Installer
