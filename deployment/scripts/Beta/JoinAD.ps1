$scriptDir = "C:\Windows\Setup\Scripts"
$scriptPath = "$scriptDir\domainJoin.ps1"
New-Item -Path $scriptDir -ItemType Directory -Force
Invoke-WebRequest -Uri https://deploysmart.dev.mspot.se/deployment/scripts/domainJoin.ps1 -OutFile "$scriptPath"
$taskName = "RunDomainJoinAtStartup"
$action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-NoProfile -ExecutionPolicy Bypass -File `"$scriptPath`""
$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -RunLevel Highest

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Force
