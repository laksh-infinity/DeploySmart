# Automate the install of NoMachine.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$url = "https://downloads.nomachine.com/download/?id=9"
$response = Invoke-WebRequest -Uri $url
$htmlContent = $response.Content
$href = [regex]::Match($htmlContent, 'id="link_download" href="([^"]+)"').Groups[1].Value
$fileName = $href -split "/" | Select-Object -Last 1
$outPath = "$tp\$fileName"
Invoke-WebRequest -Uri $href -OutFile $outPath -ErrorAction Stop
Start-Process -FilePath $outPath -ArgumentList "/VERYSILENT" -Wait -WorkingDirectory $tp
Start-Sleep -Seconds 10
Remove-Item -Path $outPath -Force
