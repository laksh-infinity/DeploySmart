# Automate the install of Google Chrome.
# Script made by Mattias Magnusson 2025-05-20
$ProgressPreference = 'SilentlyContinue'
$tp = "C:\TempPath"
if (-not (Test-Path $tp)) {
    New-Item -Path $tp -ItemType Directory -Force | Out-Null
}
$Url = "https://dl.google.com/dl/chrome/install/googlechromestandaloneenterprise64.msi"
$Installer = "googlechromestandaloneenterprise64.msi"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer
Start-Process -FilePath $tp\$Installer -ArgumentList "/qn" -Wait
Start-Sleep -Seconds 10
del $tp\$Installer