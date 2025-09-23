# Automate the installation of TotalCommander.
# Script made by Mattias Magnusson 2025-08-22
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$installerUrl = "https://totalcommander.ch/1156/tcmd1156x64.exe"
$installerPath = "C:\TempPath\tcmd1156x64.exe"
New-Item -ItemType Directory -Path "C:\TempPath" -Force | Out-Null
New-Item -ItemType Directory -Path $logDir -Force | Out-Null
Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath -UseBasicParsing
Start-Process -FilePath $installerPath -ArgumentList "/AHN*" -Wait -NoNewWindow
Remove-Item -Path $installerPath -Force
