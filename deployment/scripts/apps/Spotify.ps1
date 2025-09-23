# Automate the install of Spotify.
# Script made by Mattias Magnusson 2025-08-22
$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath" 
mkdir $tp -EA SilentlyContinue
$Installer = "SpotifyFullSetup.exe"
$Url = "http://download.spotify.com/$Installer"
Invoke-WebRequest -Uri $Url -OutFile $tp\$Installer

Start-Process -FilePath $tp\$Installer -ArgumentList "/extract `"C:\Program Files\Spotify`"" -Wait

$SourceFilePath = "C:\Program Files\Spotify\Spotify.exe"
$ShortcutPath = "C:\Users\Public\Desktop\Spotify.lnk"
$WScriptObj = New-Object -ComObject ("WScript.Shell")
$shortcut = $WscriptObj.CreateShortcut($ShortcutPath)
$shortcut.TargetPath = $SourceFilePath
$shortcut.Save()

$ShortcutPath = "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\Spotify.lnk"
$shortcut = $WscriptObj.CreateShortcut($ShortcutPath)
$shortcut.TargetPath = $SourceFilePath
$shortcut.Save()

[void](New-Item -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall' -Name 'Spotify')
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'DisplayIcon' -Value 'C:\Program Files\Spotify\Spotify.exe,0' -Type String
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'DisplayName' -Value 'Spotify' -Type String
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'DisplayVersion' -Value '1.2.70.409' -Type String
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'EstimatedSize' -Value '267264' -Type Dword
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'InstallLocation' -Value 'C:\Program Files\Spotify' -Type ExpandString
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'NoModify' -Value '1' -Type Dword
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'NoRepair' -Value '1' -Type Dword
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'Publisher' -Value 'Spotify AB' -Type String
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'UninstallString' -Value '"C:\Program Files\Spotify\Spotify.exe" /uninstall' -Type ExpandString
Set-ItemProperty -Path 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Spotify' -Name 'URLInfoAbout' -Value 'https://www.spotify.com' -Type String

Start-Sleep -Seconds 10
del $tp\$Installer
