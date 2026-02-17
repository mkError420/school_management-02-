@echo off
echo Setting up Virtual Host for School Management System...
echo.

echo Step 1: Enabling virtual hosts...
powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace '#Include conf/extra/httpd-vhosts.conf', 'Include conf/extra/httpd-vhosts.conf' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"

echo Step 2: Creating virtual host configuration...
echo ^<VirtualHost *:80^> > "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo     DocumentRoot "C:/xampp/htdocs/School management/public" >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo     ServerName school.local >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo     ServerAlias localhost >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo     ^<Directory "C:/xampp/htdocs/School management/public"^> >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo         Options Indexes FollowSymLinks >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo         AllowOverride All >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo         Require all granted >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo     ^</Directory^> >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
echo ^</VirtualHost^> >> "C:\xampp\apache\conf\extra\httpd-vhosts.conf"

echo Step 3: Adding localhost to hosts file...
echo 127.0.0.1 school.local >> C:\Windows\System32\drivers\etc\hosts

echo.
echo Virtual host configuration completed!
echo.
echo Please restart Apache in XAMPP Control Panel.
echo Then access: http://localhost/ or http://school.local/
echo.
pause
