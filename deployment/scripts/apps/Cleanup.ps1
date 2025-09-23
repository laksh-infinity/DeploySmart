# Delete C:\Windows.old if it exists.
if (Test-Path "C:\Windows.old") {
Remove-Item -Path "C:\Windows.old" -Recurse -Force
}

# Delete C:\TempPath if it exists.
if (Test-Path "C:\TempPath") {
Remove-Item -Path "C:\TempPath" -Recurse -Force
}

#Clean up the Dell CCTK Toolkit left behind after BIOS password is set.
msiexec.exe /x "{C8EA30FC-B20B-465E-9D8A-CDDC09EA72D4}" /qn /norestart