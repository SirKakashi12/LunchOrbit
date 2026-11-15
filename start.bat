@echo off
cd /d "%~dp0"

set PORT=8000

echo Starting Lunch Orbit on port %PORT%...

start http://localhost:%PORT%/signUp/view.php"

php -S localhost:%PORT% -t "%~dp0"

pause