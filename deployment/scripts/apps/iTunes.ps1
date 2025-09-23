# Automate the install of iTunes.
# Script made by Mattias Magnusson 2025-06-09
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$redirectLink = "https://www.apple.com/itunes/download/win64"
$request = [System.Net.WebRequest]::Create($redirectLink)
$request.AllowAutoRedirect=$false
$response=$request.GetResponse()
$dlLink = $response.GetResponseHeader("Location")
$file = "iTunes64Setup.exe"
$tp = "C:\TempPath"
$InstallerPath = Join-Path $tp $file
(New-Object System.Net.WebClient).DownloadFile($dlLink, $InstallerPath)
# First extract the files we need:
Start-Process $InstallerPath -Wait -ArgumentList "/extract $tp" -Verb RunAs
# Run the remaining setup files for iTunes:
Start-Process msiexec.exe -Wait -ArgumentList "/i $tp\AppleMobileDeviceSupport64.msi /qn"
Start-Process msiexec.exe -Wait -ArgumentList "/i $tp\iTunes64.msi REBOOT=ReallySuppress /qn"
Remove-Item -Path "$tp\AppleMobileDeviceSupport64.msi"
Remove-Item -Path "$tp\AppleSoftwareUpdate.msi"
Remove-Item -Path "$tp\Bonjour64.msi"
Remove-Item -Path "$tp\iTunes64.msi"
Remove-Item -Path "$tp\$file"
Remove-Item -Path "$tp\SetupAdmin.exe"