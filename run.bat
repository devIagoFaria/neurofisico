@echo off
echo Limpando cache do Laravel...
php artisan optimize:clear

echo.
echo Iniciando servidor Laravel...
php artisan serve

pause
