# Automate the install of Firefox.
# Script made by Mattias Magnusson 2025-06-09
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
mkdir $tp -ErrorAction SilentlyContinue
$Url = "https://download.mozilla.org/?product=firefox-msi-latest-ssl&os=win64&lang=en-US"
$Installer = "FirefoxSetup.msi"
Invoke-WebRequest -Uri $Url -OutFile "$tp\$Installer" -ErrorAction Stop
Start-Process -FilePath "$tp\$Installer" -ArgumentList "/q /norestart" -Wait
Start-Sleep -Seconds 30
Remove-Item -Path "$tp\$Installer" -Force