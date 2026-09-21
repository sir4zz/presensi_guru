@echo off
echo ==========================================
echo   Laravel HTTPS Dev Server
echo   URL: https://localhost:8000
echo   URL: https://192.168.x.x:8000
echo ==========================================
echo.
echo NOTE: Browser akan menampilkan peringatan sertifikat
echo       self-signed. Klik "Advanced" lalu "Proceed".
echo.
php -S 0.0.0.0:8000 -t public ssl/router.php
pause
