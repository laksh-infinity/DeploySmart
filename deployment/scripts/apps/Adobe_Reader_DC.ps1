# Automate the install of Acrobat Adobe Reader.
# Script made by Mattias Magnusson 2025-05-20
# Updated 2025-08-12 By Mattias Magnusson
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
if (-not (Test-Path $tp)) {
    New-Item -Path $tp -ItemType Directory -Force | Out-Null
}
$TwoLetterISOLanguageName = (Get-WinSystemLocale).TwoLetterISOLanguageName
$IetfLanguageTag = (Get-WinSystemLocale).IetfLanguageTag.Replace("-", "_")

# Get latest MSP version and URL
$releasePage = curl.exe -s "https://helpx.adobe.com/acrobat/release-note/release-notes-acrobat-reader.html"
$releaseMatch = [regex]::Match($releasePage, '<a href="(https://www\.adobe\.com/devnet-docs/acrobatetk/tools/ReleaseNotesDC/[^"]+)"[^>]*>(DC [^<]+)</a>', 'IgnoreCase')
$notesUrl = $releaseMatch.Groups[1].Value
$notesPage = curl.exe -s $notesUrl
$mspMatch = [regex]::Match($notesPage, '<a[^>]+href="([^"]+\.msp)"[^>]*>', 'IgnoreCase')
$mspUrl = $mspMatch.Groups[1].Value
$mspFile = Split-Path -Path $mspUrl -Leaf
$mspPath = Join-Path $tp $mspFile
$mspVersion = ($mspFile -replace '.*?(\d{10,}).*', '$1')
#(New-Object System.Net.WebClient).DownloadFile($mspUrl, $mspPath)
Invoke-WebRequest -Uri $mspUrl -OutFile $mspPath -UseBasicParsing

# Construct installer URL using MSP version
$installerFile = "AcroRdrDCx64${mspVersion}_${IetfLanguageTag}.exe"
$installerUrl = "https://ardownload2.adobe.com/pub/adobe/acrobat/win/AcrobatDC/$mspVersion/$installerFile"
$installerPath = Join-Path $tp $installerFile
#(New-Object System.Net.WebClient).DownloadFile($installerUrl, $installerPath)
Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath -UseBasicParsing

# Install and patch
Start-Process $installerPath -Wait -ArgumentList "/sAll /rs /msi EULA_ACCEPT=YES" -Verb RunAs
Start-Process "msiexec.exe" -Wait -ArgumentList "/p `"$mspPath`" /qn" -Verb RunAs

Start-Sleep -Seconds 10
Remove-Item $installerPath -Force
Remove-Item $mspPath -Force

