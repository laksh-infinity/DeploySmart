# DeploySmart
DeploySmart is an application to ease sys admin setup of new/reinstall computers by utilizing autounattend.xml and powershell to install applications from a list easier.

Alright, i have tried to clean this up to the best i can (today).

If you decide to run this, please do so on a localhost for now as there might be breaking changes as well as security issues involved, this is one of my first projects to this big.

## Default admin:
admin@example.com
Admin123456!

Don't forget to change password and enable TOTP/MFA from Profile Settings.

## How to use this:

1. Configure everything in db.php
2. Import your database in MySQL
3. Create a company (or use the default account). 
4. Generate autounattend.xml (this *SHOULD* include your DeploySmart url and ID) If it doesn't i have most likely done something wrong ^^
5. Go to "Configure Apps" you have a predefined list of applications that im sending with DeploySmart, but you can add your own directly under "Custom Scripts" easy as "Add" and "Save". 
6. Now you could run "irm https://deploysmart.yourdomain.com/deploy.php?ID={DS_YourDeploySmartID}" and that should show you the application list you just saved. 
7. Now Create a USB drive with any standard Windows 10/11 iso and put your autounattend.xml on that USB and re/install a machine.
8. Enjoy!

## Adding .ps1 scripts to the "🌐 Global" tab
All .ps1 scripts put in the /deployment/scripts/apps/ folder will automatically get added if cron is correctly setup against cron.php.

If you find something that needs to be fixed, im sorry, im just one person trying my best.

### Im currently cleaning up my old configuration files, cleaning out any eventual passwords and other sensitive stuff.
