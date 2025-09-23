# Automate the install of Opera Browser.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$versions = (Invoke-WebRequest -UseBasicParsing -Uri "https://get.geo.opera.com/pub/opera/desktop" | Select-Object -ExpandProperty Links | Select-Object -ExpandProperty href)
$version = $versions | Where-Object { $_ -match '\d+.\d+.\d+.\d+' } | ForEach-Object { if ($_ -match '(\d+.\d+.\d+.\d+)') { $matches[1] } } | Sort-Object { [version]$_ } | Select-Object -Last 1
$Installer = "Opera_$($version)_Setup_x64.exe"
$Url = "https://get.geo.opera.com/pub/opera/desktop/$version/win/$Installer"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer
Start-Process -FilePath $tp\$Installer -ArgumentList "/silent /allusers=1 /launchopera=0 /enable-installer-stats=0 /enable-stats=0" -Wait
Start-Sleep -Seconds 30
del $tp\$Installer
