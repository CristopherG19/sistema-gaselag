@echo off
echo ========================================
echo   INSTALACION SISTEMA GASELAG
echo ========================================
echo.

echo [1/8] Verificando Composer...
composer --version
if %errorlevel% neq 0 (
    echo ERROR: Composer no esta instalado
    pause
    exit /b 1
)

echo [2/8] Instalando dependencias PHP...
composer install --ignore-platform-req=ext-gd
if %errorlevel% neq 0 (
    echo ERROR: Fallo la instalacion de dependencias
    pause
    exit /b 1
)

echo [3/8] Configurando archivo .env...
if not exist .env (
    copy .env.example .env
    echo Archivo .env creado
) else (
    echo Archivo .env ya existe
)

echo [4/8] Generando clave de aplicacion...
php artisan key:generate
if %errorlevel% neq 0 (
    echo ERROR: No se pudo generar la clave
    pause
    exit /b 1
)

echo [5/8] Creando directorios necesarios...
if not exist storage\framework\views mkdir storage\framework\views
if not exist storage\framework\cache\data mkdir storage\framework\cache\data
if not exist storage\logs mkdir storage\logs
echo Directorios creados

echo [6/8] Configurando permisos...
icacls storage /grant Everyone:F /T >nul 2>&1
icacls bootstrap\cache /grant Everyone:F /T >nul 2>&1
echo Permisos configurados

echo [7/8] Ejecutando migraciones...
php artisan migrate
if %errorlevel% neq 0 (
    echo ERROR: Fallo la ejecucion de migraciones
    pause
    exit /b 1
)

echo [8/8] Limpiando cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo Cache limpiado

echo.
echo ========================================
echo   INSTALACION COMPLETADA EXITOSAMENTE
echo ========================================
echo.
echo Para iniciar el servidor ejecuta:
echo   php artisan serve
echo.
echo El sistema estara disponible en:
echo   http://localhost:8000
echo.
pause
