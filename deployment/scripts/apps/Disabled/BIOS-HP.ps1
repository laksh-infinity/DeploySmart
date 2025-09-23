$ProgressPreference = 'SilentlyContinue'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$tp = "C:\TempPath"
mkdir $tp
Invoke-WebRequest -Uri https://deploysmart.dev.mspot.se/deployment/files/sp143621.exe -OutFile $tp\sp143621.exe
$Installer = "$tp\sp143621.exe"
Start-Process -FilePath $Installer -ArgumentList "/s /f $tp" -Wait
Start-Process -FilePath "$tp\Setup.exe" -ArgumentList "/s /v /qn" -Wait
Start-Process -FilePath "$tp\HPQPswd.exe" -ArgumentList "/s /f`"$tp\BCUEQ.bin`" /p`"Nope`" "
Start-Process -FilePath "$tp\BIOSConfigUtility.exe" -ArgumentList "/nspwdfile:`"$tp\BCUEQ.bin`" /cspwdfile:`"`" "
Start-Sleep -Seconds 10
del -r $tp -Force