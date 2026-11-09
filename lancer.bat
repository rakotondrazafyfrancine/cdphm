@echo off
rem Lance CDPHM : demarrage immediat (les caches sont deja prepares, voir optimiser.bat)
cd /d "%~dp0"
rem prechauffe la page de connexion puis ouvre le navigateur, pendant que le serveur demarre
start "" /min cmd /c "timeout /t 3 /nobreak >nul & curl -s -o nul http://127.0.0.1:8000/admin/login & start http://127.0.0.1:8000"
php artisan serve --host=127.0.0.1 --port=8000
