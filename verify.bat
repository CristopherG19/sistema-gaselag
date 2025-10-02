@echo off
echo ========================================
echo   VERIFICACION SISTEMA GASELAG
echo ========================================
echo.

echo [1/5] Verificando servidor...
php artisan serve --port=8001 >nul 2>&1 &
timeout /t 3 >nul
curl -s -o nul -w "Codigo HTTP: %%{http_code}" http://localhost:8001
if %errorlevel% equ 0 (
    echo - OK
) else (
    echo - ERROR
)
taskkill /f /im php.exe >nul 2>&1

echo [2/5] Verificando base de datos...
php artisan tinker --execute="DB::connection()->getPdo();" >nul 2>&1
if %errorlevel% equ 0 (
    echo - OK
) else (
    echo - ERROR
)

echo [3/5] Verificando migraciones...
php artisan migrate:status | findstr "Pending" >nul
if %errorlevel% neq 0 (
    echo - OK
) else (
    echo - ERROR: Hay migraciones pendientes
)

echo [4/5] Verificando permisos de storage...
echo test > storage\test.txt 2>nul
if exist storage\test.txt (
    del storage\test.txt
    echo - OK
) else (
    echo - ERROR: No se puede escribir en storage
)

echo [5/5] Verificando configuracion...
php artisan config:show app.name >nul 2>&1
if %errorlevel% equ 0 (
    echo - OK
) else (
    echo - ERROR
)

echo.
echo ========================================
echo   VERIFICACION COMPLETADA
echo ========================================
pause
