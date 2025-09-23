# Automate the install of Dell CCTK and DellBIOSProvider for PowerShell to work on all systems.
# Script made by Mattias Magnusson 2025-07-09
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
mkdir $tp
cd $tp
Invoke-WebRequest -Uri https://deploysmart.dev.mspot.se/deployment/files/Systems-Management_Application_YYPGT_WN_2.2_X10.EXE -OutFile $tp\Systems-Management_Application_YYPGT_WN_2.2_X10.EXE
Invoke-WebRequest -Uri https://deploysmart.dev.mspot.se/deployment/files/DellCommandPowerShellProvider.zip -OutFile $tp\DellCommandPowerShellProvider.zip
$Installer = "$tp\Systems-Management_Application_YYPGT_WN_2.2_X10.EXE"
Start-Process -FilePath $Installer -ArgumentList "/s /e=$tp" -Wait
$msiPath = Join-Path $tp cctk.msi
Start-Process -FilePath "msiexec.exe" -ArgumentList "/i `"$msiPath`" /quiet /qn /norestart" -Wait
$cctkPath = "C:\Program Files (x86)\Dell\CCTK\X86_64"
cmd /c " `"C:\Program Files (x86)\Dell\CCTK\X86_64\cctk.exe`" --setuppwd=Nope"

$AdminPwd = "Nope"
$zipPath = ".\DellCommandPowerShellProvider.zip"
$extractPath = Join-Path $tp "DCPSP"
$destination = Join-Path ${env:ProgramFiles} "WindowsPowerShell\Modules"

if (-not (Test-Path $tp)) {
    New-Item -Path $tp -ItemType Directory | Out-Null
}

Expand-Archive -Path $zipPath -DestinationPath $extractPath -Force
$sourceFolder = Join-Path $extractPath "DellBIOSProvider"
$targetFolder = Join-Path $destination "DellBIOSProvider"
if (-not (Test-Path $destination)) {
    New-Item -Path $destination -ItemType Directory | Out-Null
}
Move-Item -Path $sourceFolder -Destination $targetFolder -Force
New-Item -Type Container -Force -Path "${env:ProgramFiles}\WindowsPowerShell\Modules"
Install-PackageProvider -Name NuGet -MinimumVersion 2.8.5.201 -Force; Install-Module -Name DellBIOSProvider -Force;
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
Import-Module DellBIOSProvider
Get-Item -Path DellSmbios:\Security\IsAdminPasswordSet
Get-Item -Path DellSmbios:\Security\IsSystemPasswordSet
Set-Item -Path DellSmbios:\Security\AdminPassword "$AdminPwd"

del -r $tp\* -Force