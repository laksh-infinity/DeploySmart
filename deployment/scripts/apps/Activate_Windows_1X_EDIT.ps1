# Automate the Activation of Windows 1X.
# Script made by Mattias Magnusson 2025-05-20
# Modified: 2025-09-20
$sys32 = "C:\Windows\System32"
cscript.exe /nologo $sys32\slmgr.vbs /skms your.kmsserver.com
cscript.exe /nologo $sys32\slmgr.vbs /ato