@echo off
echo Setting up Apache for School Management System...
echo.

echo Step 1: Backing up original httpd.conf...
copy "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd.conf.backup"

echo Step 2: Updating DocumentRoot...
powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace 'DocumentRoot \"C:/xampp/htdocs\"', 'DocumentRoot \"C:/xampp/htdocs/School management/public\"' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"

echo Step 3: Updating Directory directive...
powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace '<Directory \"C:/xampp/htdocs\">', '<Directory \"C:/xampp/htdocs/School management/public\">' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"

echo.
echo Apache configuration updated successfully!
echo.
echo Please restart Apache in XAMPP Control Panel.
echo Then access: http://localhost/
echo.
pause
