# Automate the Activation of Windows Server 2025 Standard.
# Script made by Mattias Magnusson 2025-09-10
$sys32 = "C:\Windows\System32"
cscript.exe /nologo $sys32\slmgr.vbs /ipk TVRH6-WHNXV-R9WG3-9XRFY-MY832
cscript.exe /nologo $sys32\slmgr.vbs /skms Your.KMSServer.com
cscript.exe /nologo $sys32\slmgr.vbs /ato