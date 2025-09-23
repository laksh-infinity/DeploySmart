$scriptDir = "C:\Windows\Setup\Scripts"
$scriptPath = "$scriptDir\Add_Administrator.ps1"
New-Item -Path $scriptDir -ItemType Directory -Force
Invoke-WebRequest -Uri https://deploysmart.dev.mspot.se/deployment/scripts/Add_Administrator.ps1 -OutFile "$scriptPath"
$taskName = "SetAdministrators"
$action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-NoProfile -ExecutionPolicy Bypass -File `"$scriptPath`""
$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -RunLevel Highest
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Force
Start-Sleep -Seconds 10
$domain = "user.ki.se"
$oupath = "OU=Mobile,OU=Computers,OU=MMK,DC=user,DC=domain,DC=se"
$secPassword = ConvertTo-SecureString "My$ecuR3P@$$W0d" -AsPlainText -Force
$userName = "$domain\ServiceAccount"
$credential = New-Object System.Management.Automation.PSCredential ($userName, $secPassword)
Add-Computer -DomainName $domain -OUPath $oupath -Credential $credential -Restart