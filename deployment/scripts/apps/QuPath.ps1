# Automate the install of QuPath.
# Script made by Mattias Magnusson 2025-09-23
$ProgressPreference='SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12
$tp='C:\TempPath'
$id='C:\Program Files\QuPath'
$sm="$env:ProgramData\Microsoft\Windows\Start Menu\Programs\QuPath"
mkdir $tp -ea SilentlyContinue
mkdir $id -ea SilentlyContinue
mkdir $sm -ea SilentlyContinue
$r=Invoke-RestMethod 'https://api.github.com/repos/qupath/qupath/releases/latest'
$a=$r.assets|?{ $_.name -like '*Windows.zip' }|Select-Object -First 1
$version=($a.name -replace '^QuPath-', '') -replace '-Windows.zip$', ''
$zp=Join-Path $tp $a.name
Invoke-WebRequest $a.browser_download_url -OutFile $zp
Expand-Archive $zp -DestinationPath $id -Force
$me=Get-ChildItem $id -Filter 'QuPath-*.exe'|?{ $_.Name -notlike '*console*' }|Select-Object -First 1
$ce=Get-ChildItem $id -Filter 'QuPath-*.exe'|?{ $_.Name -like '*console*' }|Select-Object -First 1
$s=New-Object -ComObject WScript.Shell
if ($me) { $sc=$s.CreateShortcut("$sm\QuPath.lnk");$sc.TargetPath=$me.FullName;$sc.WorkingDirectory=$id;$sc.Save() }
if ($ce) { $sc=$s.CreateShortcut("$sm\QuPath (console).lnk");$sc.TargetPath=$ce.FullName;$sc.WorkingDirectory=$id;$sc.Save() }
$uninstallScriptPath="$id\uninstall-qupath.ps1"
$uninstallScript=@'
$installDir = "C:\Program Files\QuPath"
$startMenu = "$env:ProgramData\Microsoft\Windows\Start Menu\Programs\QuPath"
$qupathProcesses = Get-Process -Name "QuPath-*" -ErrorAction SilentlyContinue
if ($qupathProcesses) {
    $qupathProcesses | ForEach-Object { Stop-Process -Id $_.Id -Force }
}
Start-Sleep -Seconds 10 
Remove-Item -Path $installDir -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path $startMenu -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\QuPath" -Force
'@
Set-Content -Path $uninstallScriptPath -Value $uninstallScript
$uninstallKey="HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\QuPath"
New-Item -Path $uninstallKey -Force | Out-Null
Set-ItemProperty -Path $uninstallKey -Name "DisplayName" -Value "QuPath"
Set-ItemProperty -Path $uninstallKey -Name "UninstallString" -Value "powershell.exe -ExecutionPolicy Bypass -File `"$uninstallScriptPath`""
Set-ItemProperty -Path $uninstallKey -Name "DisplayVersion" -Value $version
Set-ItemProperty -Path $uninstallKey -Name "Publisher" -Value "QuPath"
Set-ItemProperty -Path $uninstallKey -Name "InstallLocation" -Value $id
Set-ItemProperty -Path $uninstallKey -Name "EstimatedSize" -Value 165888 -Type DWord
Set-ItemProperty -Path $uninstallKey -Name "NoModify" -Value 1 -Type DWord
Set-ItemProperty -Path $uninstallKey -Name "NoRepair" -Value 1 -Type DWord
Remove-Item $zp -Force
