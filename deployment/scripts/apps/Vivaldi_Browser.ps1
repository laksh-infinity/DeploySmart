# Automate the install of Vivaldi Browser.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$Url = Invoke-WebRequest -UseBasicParsing -Uri "https://vivaldi.com/download" | Select-Object -ExpandProperty Links | Where-Object {$_.href -like "*x64.exe"} | Select-Object -ExpandProperty href | Select-Object -First 1
$Installer = "Vivaldi_Setup_x64.exe"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer
Start-Process -FilePath $tp\$Installer -ArgumentList "--vivaldi-silent --do-not-launch-chrome --system-level" -Wait
Start-Sleep -Seconds 30
del $tp\$Installer
