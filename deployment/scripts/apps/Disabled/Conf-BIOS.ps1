$SerialNumber = (Get-WmiObject -Class Win32_SystemEnclosure | Select-Object -ExpandProperty SerialNumber)
if ($SerialNumber -like "5CG*") {
    # Run script for serials starting with 5CG
    Write-Host "Serial starts with 5CG. Running HP BIOS Script"
    irm https://deploysmart.dev.mspot.se/deployment/scripts/apps/BIOS-HP.ps1 | iex
} else {
    # Run alternative script
    Write-Host "Serial does not start with 5CG. Running Dell BIOS Script"
    irm https://deploysmart.dev.mspot.se/deployment/scripts/apps/BIOS-Dell.ps1 | iex
}
