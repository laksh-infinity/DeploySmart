# Automate the install of Krita.
# Script made by Mattias Magnusson 2025-06-09
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
mkdir $tp
$htmlContent = Invoke-WebRequest -Uri "https://krita.org/en/download/"
$downloadUrl = $htmlContent.Content | Select-String -Pattern 'https://download\.kde\.org/stable/krita/.*?/krita-x64-.*?-setup\.exe' | ForEach-Object { $_.Matches.Groups[0].Value }
$installerPath = "$tp\KritaInstaller.exe"
Invoke-WebRequest -Uri $downloadUrl -OutFile $installerPath
Start-Process -FilePath $installerPath -ArgumentList "/S /AllUsers /norestart" -Wait
Start-Sleep -Seconds 10
del $installerPath
