@echo off
rem Remet la base de donnees dans l'etat de reference (apres la repetition ou la simulation).
rem Les lots, bons et factures crees depuis sont supprimes. Fichier utilise : database\sauvegardes\cdphm_etat_soutenance.sql
cd /d "%~dp0"
if not exist "database\sauvegardes\cdphm_etat_soutenance.sql" (
    echo Sauvegarde introuvable : database\sauvegardes\cdphm_etat_soutenance.sql
    pause
    exit /b 1
)
echo ATTENTION : tout ce qui a ete saisi depuis la sauvegarde sera efface.
set /p ok=Taper O pour continuer :
if /i not "%ok%"=="O" (
    echo Annule : rien n'a ete modifie.
    pause
    exit /b 0
)
"C:\xampp83\mysql\bin\mysql.exe" --host=127.0.0.1 --port=3306 --user=root cdphm < "database\sauvegardes\cdphm_etat_soutenance.sql"
if errorlevel 1 (
    echo Echec de la restauration. Verifie que MySQL (XAMPP) est demarre.
    pause
    exit /b 1
)
call php artisan cache:clear
echo.
echo Base remise a l'etat de reference (487 lots). Tu peux relancer lancer.bat.
pause
