# Automate the install of Microsoft Office 365.
# Script made by Mattias Magnusson 2025-06-09
$ProgressPreference = 'SilentlyContinue'
$tp ="C:\TempPath"
Invoke-WebRequest -Uri "https://c2rsetup.officeapps.live.com/c2r/download.aspx?ProductreleaseID=O365ProPlusRetail&language=en-us&platform=def&version=O16GA&source=MktDownloadForWinPage" -OutFile $tp\OfficeSetup.exe
Invoke-WebRequest -Uri https://deploysmart.dev.mspot.se/deployment/files/O24LTSC-configuration.xml -OutFile $tp\O24LTSC-configuration.xml
cd $tp
Start-Process -FilePath $tp\OfficeSetup.exe -ArgumentList "/configure $tp\O24LTSC-configuration.xml" -Wait
Start-Sleep -s 10
del $tp\OfficeSetup.exe; del $tp\Configuration.xml