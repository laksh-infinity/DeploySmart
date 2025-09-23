# Automate the Activation of Office Professional Plus 2024 LTSC.
# Script made by Mattias Magnusson 2025-08-22
$opath = "C:\Program Files\Microsoft Office\Office16\"
cscript //nologo $opath'ospp.vbs' /sethst:Your.KMSServer.com
Start-Sleep -s 10
cscript //nologo $opath'ospp.vbs' /cachst:TRUE
Start-Sleep -s 10
cscript //nologo $opath'ospp.vbs' /act