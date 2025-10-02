#!/bin/bash

echo "========================================"
echo "   INSTALACION SISTEMA GASELAG"
echo "========================================"
echo

echo "[1/8] Verificando Composer..."
if ! command -v composer &> /dev/null; then
    echo "ERROR: Composer no está instalado"
    exit 1
fi
composer --version

echo "[2/8] Instalando dependencias PHP..."
composer install --ignore-platform-req=ext-gd
if [ $? -ne 0 ]; then
    echo "ERROR: Falló la instalación de dependencias"
    exit 1
fi

echo "[3/8] Configurando archivo .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Archivo .env creado"
else
    echo "Archivo .env ya existe"
fi

echo "[4/8] Generando clave de aplicación..."
php artisan key:generate
if [ $? -ne 0 ]; then
    echo "ERROR: No se pudo generar la clave"
    exit 1
fi

echo "[5/8] Creando directorios necesarios..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
echo "Directorios creados"

echo "[6/8] Configurando permisos..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
echo "Permisos configurados"

echo "[7/8] Creando base de datos..."
echo "IMPORTANTE: Asegúrate de crear la base de datos 'sistema_gaselag' en MySQL"
echo "Ejecutar: CREATE DATABASE IF NOT EXISTS sistema_gaselag CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
read -p "Presiona Enter después de crear la base de datos..."

echo "[8/9] Ejecutando migraciones..."
php artisan migrate
if [ $? -ne 0 ]; then
    echo "ERROR: Falló la ejecución de migraciones"
    exit 1
fi

echo "[9/9] Limpiando cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "Cache limpiado"

echo
echo "========================================"
echo "   INSTALACION COMPLETADA EXITOSAMENTE"
echo "========================================"
echo
echo "Para iniciar el servidor ejecuta:"
echo "  php artisan serve"
echo
echo "El sistema estará disponible en:"
echo "  http://localhost:8000"
echo
