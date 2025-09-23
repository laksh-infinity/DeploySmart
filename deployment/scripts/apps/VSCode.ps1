# Automate the install of Microsoft Visual Studio Code.
# Script made by Mattias Magnusson 2025-06-09
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
mkdir $tp -Force
$installerUrl = "https://update.code.visualstudio.com/latest/win32-x64-user/stable"
$installerPath = "$tp\VSCodeSetup.exe"
Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath
Start-Process -FilePath $installerPath -ArgumentList "/silent /mergetasks=!runcode" -Wait
Start-Sleep -Seconds 30
Remove-Item $installerPath
