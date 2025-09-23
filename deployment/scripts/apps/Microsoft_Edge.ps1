# Automate the install of Microsoft Edge Enterprise.
# Script made by Mattias Magnusson 2025-05-20
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
if (-not (Test-Path $tp)) {
    New-Item -ItemType Directory -Path $tp | Out-Null
}
Invoke-WebRequest -Uri "https://go.microsoft.com/fwlink/?linkid=2093437" -OutFile "$tp\MicrosoftEdgeEnterpriseX64.msi"
$file = "MicrosoftEdgeEnterpriseX64.msi"
$InstallerPath = Join-Path $tp $file
Start-Process msiexec.exe -Wait -ArgumentList "/i `"$InstallerPath`" /qn" -Verb RunAs
Remove-Item $InstallerPath
