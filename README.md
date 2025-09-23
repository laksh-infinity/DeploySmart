# $$\textsf{\color{white}Deploy\textsf{\color{#0fda00}Smart}}$$

DeploySmart is an application to ease sys admin setup of new/reinstall computers by utilizing autounattend.xml and PowerShell to install applications from a .json list.

The goal of this project is to make things easier and faster for anyone setting up new computers or re-installing Windows often, may or may not need complex software automatically installed from scratch or even need to install/deploy for multiple companies or inhouse departments with different needs.

If you decide to run this, please do so on a localhost for now as there might be breaking changes as well as security issues involved, this is one of my first projects this big too.

## Prerequisites:

1. PHP 8.2+
2. Mysql 8+
3. Apache2/Nginx
4. Windows 10/11 to run the autounattend.xml and the installer scripts.

## How to use this:

1. Configure everything in db.php
2. Import your database in MySQL
3. Create a company (or use the default account). 
4. Generate autounattend.xml (this *SHOULD* include your DeploySmart url and ID) If it doesn't i have most likely done something wrong.
5. Go to "Configure Apps" you have a predefined list of applications that im sending with DeploySmart, but you can add your own directly under "Custom Scripts" easy as "Add" and "Save". 
6. Now you could run "irm https://deploysmart.yourdomain.com/deploy.php?ID={DS_YourDeploySmartID}" and that should show you the application list you just saved. 
7. Now Create a USB drive with any standard Windows 10/11 iso and put your autounattend.xml on that USB and re/install a machine.
8. Enjoy!

## Default login:

admin@example.com

Admin123456!

Don't forget to change password and enable TOTP/MFA from Profile Settings.

## Adding .ps1 scripts to the "🌐 Global" tab
All .ps1 scripts put in the /deployment/scripts/apps/ folder will automatically get added if cron is correctly setup against cron.php.

If you find something that needs to be fixed, im sorry, im just one person trying my best.

## What's next?

1. Installer script to make it easier yo set up.
2. Internal guide to make the learning curve fast and easy.
3. UI/UX changes to have abetter over all style and feel (Mobile variant).
4. More automated applications.

## Issues?

Please use the issue tracker here. No guidelines for now, all information is good information.
