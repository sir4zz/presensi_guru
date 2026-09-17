@echo off
echo ==========================================
echo   Laravel Dev Server (HTTP)
echo   URL: http://localhost:8000
echo ==========================================
echo.
echo   Untuk akses dari HP via LAN:
echo   1. Buka http://<IP-KOMPUTER>:8000
echo   2. Kamera GPS butuh HTTPS di HP
echo   3. Jalankan serve-https.bat untuk HTTPS
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause
