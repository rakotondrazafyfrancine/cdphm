@echo off
rem A lancer apres une modification du code ou du fichier .env (prend 1 a 2 minutes, une seule fois)
cd /d "%~dp0"
call php artisan optimize:clear
call php artisan config:cache
call php artisan route:cache
call php artisan event:cache
call php artisan view:cache
call php artisan icons:cache
call php artisan filament:cache-components
echo.
echo Caches prets. Relance ensuite lancer.bat
pause
