# Script made by Mattias Magnusson 2025-08-21
# Checks and installs Windows Updates after application installation is done. 
# Doing this before application installations can and will result in applications failing to install.
# Updated 2025-08-21

function Get-And-Install-AllUpdates {
    $timestamp = Get-Date -Format "yy-MM-dd HH-mm"
    $logPath = "C:\logs\Update $timestamp.log"

    if (-not (Test-Path "C:\logs")) {
        New-Item -Path "C:\logs" -ItemType Directory -Force | Out-Null
    }

    function Log($message) {
        $timestampNow = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        "$timestampNow - $message" | Out-File -FilePath $logPath -Append -Encoding UTF8
        Write-Output $message
    }

    try {
        Log "Creating Microsoft.Update.Session COM object"
        $session = New-Object -ComObject Microsoft.Update.Session -ErrorAction Stop
        $searcher = $session.CreateUpdateSearcher()

        # Force use of Microsoft Update instead of WSUS
        Log "Forcing use of Microsoft Update instead of WSUS..."
        $serviceManager = New-Object -ComObject Microsoft.Update.ServiceManager
        $serviceManager.ClientApplicationID = "PowerShell Script"

        $muService = $serviceManager.Services | Where-Object { $_.Name -eq "Microsoft Update" }
        if (-not $muService) {
            Log "Registering Microsoft Update service..."
            $serviceManager.AddService2("7971f918-a847-4430-9279-4a52d1efe18d", 7, "")
        } else {
            Log "Microsoft Update service already registered."
        }

        Log "Searching for all missing updates... (Windows, Drivers and Security Definitions)."
        $searchResult = $searcher.Search("IsInstalled=0")

        $updates = $searchResult.Updates
        Log "Total updates found: $($updates.Count)"
        if ($updates.Count -eq 0) {
            Log "No missing updates found."
            return
        }

        $updatesToDownload = New-Object -ComObject Microsoft.Update.UpdateColl
        foreach ($update in $updates) {
            if (-not $update.IsDownloaded) {
                Log "Adding to download list: $($update.Title)"
                $updatesToDownload.Add($update) | Out-Null
            }
        }

        if ($updatesToDownload.Count -gt 0) {
            Log "Downloading updates..."
            $downloader = $session.CreateUpdateDownloader()
            $downloader.Updates = $updatesToDownload
            $downloader.Download()
        } else {
            Log "All updates already downloaded."
        }

        $updatesToInstall = New-Object -ComObject Microsoft.Update.UpdateColl
        foreach ($update in $updates) {
            if ($update.IsDownloaded -and -not $update.IsInstalled) {
                Log "Adding to install list: $($update.Title)"
                $updatesToInstall.Add($update) | Out-Null
            }
        }

        if ($updatesToInstall.Count -gt 0) {
            Log "Installing updates..."
            $installer = $session.CreateUpdateInstaller()
            $installer.Updates = $updatesToInstall
            $installationResult = $installer.Install()

            Log "Installation Result Code: $($installationResult.ResultCode)"
            Log "Reboot Required: $($installationResult.RebootRequired)"

            for ($i = 0; $i -lt $updatesToInstall.Count; $i++) {
                $update = $updatesToInstall.Item($i)
                $result = $installationResult.GetUpdateResult($i)
                $code = $result.ResultCode
                $codeMeaning = switch ($code) {
                    0 { "Not Started" }
                    1 { "In Progress" }
                    2 { "Succeeded" }
                    3 { "Succeeded With Errors" }
                    4 { "Failed" }
                    5 { "Aborted" }
                    default { "Unknown" }
                }

                Log "Update: $($update.Title)"
                Log "  Result Code: $code ($codeMeaning)"
                Log "  Reboot Required: $($result.RebootRequired)"

                if (($code -eq 2 -or $code -eq 3) -and $update.Title -like "*Defender*") {
                    Log "  Note: Result code $code is common for Defender updates and usually not a problem."
                    Log "  Log file written to $logPath"
                }
            }

            if ($installationResult.RebootRequired) {
                Log "Reboot is required, but will not be forced by this script."
            }

        } else {
            Log "No updates to install."
        }

    } catch {
        Log "Fatal Exception: $($_.Exception.Message)"
    }
}

# Run the function
Get-And-Install-AllUpdates
