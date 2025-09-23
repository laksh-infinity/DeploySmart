# Automate* the Hostname of new computers.
# Script made by Mattias Magnusson 2025-05-20

Add-Type -AssemblyName System.Windows.Forms

# Create the form
$form = New-Object System.Windows.Forms.Form
$form.Text = "Hostname Generator"
$form.Size = New-Object System.Drawing.Size(400,200)
$form.StartPosition = "CenterScreen"

# Set a font that supports Unicode (like Segoe UI)
$font = New-Object System.Drawing.Font("Segoe UI", 10)

# Apply the font to all controls
$form.Font = $font
$serialLabel.Font = $font
$inputLabel.Font = $font
$textBox.Font = $font
$button.Font = $font

# Label for Serial Number
$serialLabel = New-Object System.Windows.Forms.Label
$serialLabel.Location = New-Object System.Drawing.Point(10,20)
$serialLabel.Size = New-Object System.Drawing.Size(360,20)
$SerialNumber = (Get-WmiObject -Class Win32_SystemEnclosure | Select-Object -ExpandProperty SerialNumber)
$serialLabel.Text = "Computer ST/SN: $SerialNumber"
$form.Controls.Add($serialLabel)

# Label for input
$inputLabel = New-Object System.Windows.Forms.Label
$inputLabel.Location = New-Object System.Drawing.Point(10,50)
$inputLabel.Size = New-Object System.Drawing.Size(360,20)
$inputLabel.Text = "Input user research group (3 digits):"
$form.Controls.Add($inputLabel)

# TextBox for input
$textBox = New-Object System.Windows.Forms.TextBox
$textBox.Location = New-Object System.Drawing.Point(10,75)
$textBox.Size = New-Object System.Drawing.Size(360,20)
$form.Controls.Add($textBox)

# Button
$button = New-Object System.Windows.Forms.Button
$button.Location = New-Object System.Drawing.Point(10,110)
$button.Size = New-Object System.Drawing.Size(100,30)
$button.Text = "Confirm"
$form.Controls.Add($button)

# Event handler
$button.Add_Click({
    $userInput = $textBox.Text
    if ($userInput -match '^\d{3}$') {
        $Hostname = "HL$userInput$SerialNumber"
        [System.Windows.Forms.MessageBox]::Show("New Hostname: $Hostname`nRebooting in 30 seconds.")
        Start-Sleep -Seconds 30
        Rename-Computer -NewName $Hostname -Restart
        $form.Close()
    } else {
        [System.Windows.Forms.MessageBox]::Show("ERROR! Input exactly 3 digits (0-9).")
    }
})

# Show the form
$form.Topmost = $true
$form.Add_Shown({$form.Activate()})
[void]$form.ShowDialog()
