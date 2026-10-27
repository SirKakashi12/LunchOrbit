@echo off
cd /d %~dp0

set PORT=8000

echo Starting server on port %PORT%...
start http://localhost:%PORT%

php -S localhost:%PORT%
pause