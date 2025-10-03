# Sistema de Gestión de Remesas GASELAG

![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)

Sistema web desarrollado en Laravel para la gestión de remesas de archivos DBF de SEDAPAL, con funcionalidades de carga, procesamiento, edición y administración de datos.

## 🚀 Características Principales

### 📊 **Gestión de Remesas**
- **Carga de archivos DBF** (máximo 50MB)
- **Procesamiento automático** de datos de SEDAPAL
- **Flujo de dos pasos**: Subir → Configurar → Procesar
- **Validación de duplicados** por número de carga
- **Generación automática de códigos OC**

### 👥 **Sistema de Roles**
- **Administrador**: Acceso completo (cargar, editar, eliminar, gestionar)
- **Usuario Normal**: Solo visualización de datos

### 🔧 **Funcionalidades Avanzadas**
- **Edición de registros** individuales con historial
- **Gestión masiva** de registros con selección múltiple
- **Edición de metadatos** de remesas
- **Filtros avanzados** por centro de servicio, fecha, cliente
- **Paginación optimizada** y responsive
- **Dashboard con estadísticas** en tiempo real

### 📱 **Interfaz de Usuario**
- **Diseño responsive** con Bootstrap 5
- **Navegación intuitiva** con navbar unificado
- **Componentes reutilizables** (paginación, navbar)
- **Iconos Bootstrap** para mejor UX
- **Alertas y notificaciones** contextuales

## 🛠️ Requisitos del Sistema

### **Servidor Web**
- **Apache** 2.4+ o **Nginx** 1.18+
- **PHP** 8.2 o superior
- **MySQL** 8.0+ o **MariaDB** 10.3+

### **Extensiones PHP Requeridas**
```bash
- BCMath PHP Extension
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
```

### **Herramientas de Desarrollo**
- **Composer** 2.0+
- **Node.js** 16+ (para assets)
- **NPM** 8+ o **Yarn** 1.22+

## 📦 Instalación

## 🚀 Instalación Rápida (Windows + XAMPP)

### **Opción A: Instalación Automatizada (Recomendada)**

```batch
# 1. Clonar repositorio
git clone https://github.com/CristopherG19/sistema-gaselag.git
cd sistema-gaselag

# 2. Ejecutar script de instalación
install.bat

# 3. Crear base de datos en MySQL Workbench
# Ejecutar: CREATE DATABASE IF NOT EXISTS BD_GASELAG_SISTEMA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Configurar .env (cambiar DB_PASSWORD=root si es necesario)

# 5. Iniciar servidor
php artisan serve
```

### **Opción B: Instalación Manual**

```powershell
# 1. Clonar repositorio
git clone https://github.com/CristopherG19/sistema-gaselag.git
cd sistema-gaselag

# 2. Instalar dependencias (ignorar error GD)
composer install --ignore-platform-req=ext-gd

# 3. Configurar entorno
Copy-Item .env.example .env
php artisan key:generate

# 4. Crear base de datos en MySQL Workbench
# Ejecutar: CREATE DATABASE IF NOT EXISTS BD_GASELAG_SISTEMA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 5. Configurar .env (cambiar DB_PASSWORD=root si es necesario)

# 6. Ejecutar migraciones
php artisan migrate

# 7. Configurar permisos
icacls storage /grant Everyone:F /T
icacls bootstrap\cache /grant Everyone:F /T

# 8. Iniciar servidor
php artisan serve
```

**¡Listo!** El sistema estará en `http://localhost:8000`

### **Verificación Post-Instalación**

```batch
# Ejecutar script de verificación
verify.bat
```

---

## 📦 Instalación Detallada

### **Prerrequisitos**
- **XAMPP** instalado y ejecutándose (Apache + MySQL)
- **Composer** instalado globalmente
- **PHP 8.2+** (incluido en XAMPP)
- **MySQL** ejecutándose en puerto 3306

### **1. Clonar el Repositorio**
```bash
git clone https://github.com/CristopherG19/sistema-gaselag.git
cd sistema-gaselag
```

### **2. Instalar Dependencias**
```bash
# Dependencias PHP (ignorar error de extensión GD si aparece)
composer install --ignore-platform-req=ext-gd

# Dependencias Node.js (opcional para desarrollo)
npm install
```

### **3. Configurar Variables de Entorno**

#### **Windows (PowerShell):**
```powershell
# Copiar archivo de configuración
Copy-Item .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

#### **Linux/Mac:**
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### **4. Configurar Base de Datos**

#### **Opción A: Usando MySQL Workbench (Recomendado)**
1. Abrir MySQL Workbench
2. Conectarse al servidor local (puerto 3306)
3. Ejecutar el siguiente SQL:
```sql
CREATE DATABASE IF NOT EXISTS BD_GASELAG_SISTEMA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### **Opción B: Usando línea de comandos**
```bash
# Windows (usando XAMPP)
C:\xampp\mysql\bin\mysql.exe -u root -p -e "CREATE DATABASE IF NOT EXISTS BD_GASELAG_SISTEMA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Linux/Mac
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS BD_GASELAG_SISTEMA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### **Configurar archivo .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=BD_GASELAG_SISTEMA
DB_USERNAME=root
DB_PASSWORD=tu_password_mysql
```

### **5. Ejecutar Migraciones**
```bash
# Crear tablas de la base de datos
php artisan migrate

# Si hay error de columna duplicada, ejecutar:
php artisan migrate:status
# Marcar migración problemática como ejecutada si es necesario

# (Opcional) Poblar con datos de prueba
php artisan db:seed
```

### **6. Configurar Permisos**

#### **Windows (PowerShell):**
```powershell
# Dar permisos completos a storage y bootstrap/cache
icacls storage /grant Everyone:F /T
icacls bootstrap\cache /grant Everyone:F /T
```

#### **Linux/Mac:**
```bash
# Dar permisos de escritura a storage y bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **7. Iniciar Servidor de Desarrollo**
```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

## ✅ Checklist de Verificación

Antes de usar el sistema, verifica que todo esté funcionando:

### **1. Verificar Servidor**
```bash
# El servidor debe estar ejecutándose
php artisan serve
# Debe mostrar: "Server running on [http://127.0.0.1:8000]"
```

### **2. Verificar Base de Datos**
```bash
# Probar conexión a MySQL
php artisan tinker --execute="DB::connection()->getPdo();"
# No debe mostrar errores
```

### **3. Verificar Migraciones**
```bash
# Verificar que todas las migraciones estén ejecutadas
php artisan migrate:status
# Todas deben mostrar "Ran"
```

### **4. Verificar Permisos**
```bash
# Verificar que storage sea escribible
php artisan tinker --execute="file_put_contents('storage/test.txt', 'test'); unlink('storage/test.txt');"
# No debe mostrar errores
```

### **5. Verificar Aplicación**
- Abrir `http://localhost:8000` en el navegador
- Debe cargar la página sin errores
- Verificar que no aparezcan errores 500 o 404

## 🚨 Solución de Problemas Comunes

### **Error: "ext-gd extension missing"**
```bash
# Solución: Instalar con flag de ignorar
composer install --ignore-platform-req=ext-gd
```

### **Error: "Access denied for user 'root'@'localhost'"**
- Verificar que MySQL esté ejecutándose en XAMPP
- Verificar usuario y contraseña en `.env`
- Asegurarse de que la base de datos `BD_GASELAG_SISTEMA` existe

### **Error: "Unknown database 'BD_GASELAG_SISTEMA'"**
- Crear la base de datos manualmente en MySQL Workbench
- O usar el archivo `create_db.sql` incluido en el proyecto

### **Error: "Duplicate column name 'rol'"**
```bash
# Marcar migración como ejecutada manualmente
php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '2025_10_01_144731_add_role_to_usuarios_table', 'batch' => 1]);"
php artisan migrate
```

### **Error: "Permission denied" en Windows**
```powershell
# Ejecutar PowerShell como Administrador
icacls storage /grant Everyone:F /T
icacls bootstrap\cache /grant Everyone:F /T
```

## 🗄️ Estructura de Base de Datos

### **Tablas Principales**

#### **`usuarios`**
- Gestión de usuarios del sistema
- Roles: `admin` | `usuario`
- Autenticación y autorización

#### **`remesas`**
- Registros procesados de remesas
- Datos completos de clientes SEDAPAL
- Metadatos de archivos DBF

#### **`remesas_pendientes`**
- Archivos subidos pendientes de procesar
- Datos temporales en formato JSON
- Se eliminan automáticamente al procesar

#### **`cambio_passwords`**
- Historial de cambios de contraseña
- Auditoría de seguridad

#### **`login_logs`**
- Registro de accesos al sistema
- Trazabilidad de usuarios

## 🚀 Funcionalidades Detalladas

### **📤 Carga de Remesas**
1. **Subir archivo DBF** (validación automática)
2. **Vista previa** de datos parseados
3. **Selección de centro de servicio**
4. **Procesamiento masivo** con generación de OC
5. **Validación de duplicados**

### **👁️ Visualización de Datos**
- **Lista de remesas** con filtros avanzados
- **Vista general** con estadísticas
- **Registros detallados** por remesa
- **Paginación optimizada**

### **✏️ Edición y Gestión**
- **Edición individual** de registros
- **Historial de cambios** por registro
- **Gestión masiva** con selección múltiple
- **Edición de metadatos** de remesas

### **👤 Administración de Usuarios**
- **Sistema de roles** (Admin/Usuario)
- **Gestión de permisos** granular
- **Historial de contraseñas**
- **Logs de acceso**

## 🔒 Seguridad

### **Autenticación**
- Sistema de login seguro
- Middleware de autenticación
- Protección de rutas sensibles

### **Autorización**
- Control de acceso basado en roles
- Middleware `CheckRole`
- Validación de permisos

### **Validación de Datos**
- Validación de archivos DBF
- Sanitización de inputs
- Protección CSRF

## 🐛 Solución de Problemas Avanzados

### **Problemas de Configuración**

#### **Error: "Class not found" o "Composer autoload"**
```bash
# Regenerar autoload de Composer
composer dump-autoload

# Limpiar caché de Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### **Error: "No application encryption key has been specified"**
```bash
# Generar clave de aplicación
php artisan key:generate

# Verificar que la clave esté en .env
php artisan config:show app.key
```

#### **Error: "SQLSTATE[HY000] [2002] Connection refused"**
- Verificar que MySQL esté ejecutándose en XAMPP
- Verificar puerto 3306 en el archivo `.env`
- Probar conexión: `php artisan tinker --execute="DB::connection()->getPdo();"`

#### **Error: "SQLSTATE[42S02] Base table or view not found"**
```bash
# Verificar estado de migraciones
php artisan migrate:status

# Ejecutar migraciones faltantes
php artisan migrate

# Si hay problemas, recrear base de datos
php artisan migrate:fresh
```

### **Problemas de Rendimiento**

#### **Error de Memoria PHP**
```ini
# En php.ini (XAMPP)
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

#### **Error: "Maximum execution time exceeded"**
```bash
# Aumentar tiempo de ejecución temporalmente
php -d max_execution_time=300 artisan migrate
```

### **Problemas de Archivos**

#### **Error: "Permission denied" en Windows**
```powershell
# Ejecutar PowerShell como Administrador
icacls storage /grant Everyone:F /T
icacls bootstrap\cache /grant Everyone:F /T

# O cambiar propietario
takeown /f storage /r /d y
takeown /f bootstrap\cache /r /d y
```

#### **Error: "File not found" en storage**
```bash
# Crear directorios necesarios
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Crear enlaces simbólicos
php artisan storage:link
```

### **Problemas de Base de Datos**

#### **Error: "Duplicate column name" en migraciones**
```bash
# Ver estado de migraciones
php artisan migrate:status

# Marcar migración problemática como ejecutada
php artisan tinker --execute="DB::table('migrations')->insert(['migration' => 'nombre_migracion_problematica', 'batch' => 1]);"

# Continuar con migraciones
php artisan migrate
```

#### **Error: "Table doesn't exist"**
```bash
# Verificar conexión a base de datos
php artisan tinker --execute="DB::select('SHOW TABLES');"

# Recrear todas las tablas
php artisan migrate:fresh
```

### **Problemas de Servidor**

#### **Error: "Address already in use" (puerto 8000)**
```bash
# Encontrar proceso usando puerto 8000
netstat -ano | findstr :8000

# Matar proceso (Windows)
taskkill /PID [numero_pid] /F

# O usar puerto diferente
php artisan serve --port=8001
```

#### **Error: "Class 'PDO' not found"**
- Verificar que extensión PDO esté habilitada en `php.ini`
- En XAMPP: descomentar `extension=pdo_mysql` en `php.ini`
- Reiniciar Apache en XAMPP

#### **Error: "Please provide a valid cache path"**
```bash
# Crear archivo de configuración de vistas
# Crear config/view.php con configuración correcta

# Crear directorios necesarios
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data

# Cambiar caché a archivos en config/cache.php
# 'default' => env('CACHE_STORE', 'file'),

# Limpiar toda la caché
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📝 Changelog

### **v1.1.0** - 2025-10-03
- ✅ **Cambio de nombre de base de datos**: `sistema_login` → `BD_GASELAG_SISTEMA`
- ✅ **Corrección procesamiento remesas masivas**: Ahora cada remesa se procesa individualmente
- ✅ **Mejoras en gestión de entregas y quejas**: Nuevas funcionalidades agregadas
- ✅ **Documentación actualizada**: READMEs y scripts de instalación actualizados
- ✅ **Migraciones optimizadas**: Nuevos índices para mejor rendimiento

### **v1.0.0** - 2025-10-01
- ✅ Sistema base de gestión de remesas
- ✅ Carga y procesamiento de archivos DBF
- ✅ Sistema de roles (Admin/Usuario)
- ✅ Interfaz responsive con Bootstrap 5
- ✅ Funcionalidades de edición y gestión
- ✅ Dashboard con estadísticas
- ✅ Sistema de paginación optimizado
- ✅ Corrección de duplicados en vista

## 📞 Soporte

### **Contacto**
- **Desarrollador**: Cristopher Gutierrez
- **Email**: cgutierrez@gaselag.com
- **GitHub**: [@CristopherG19](https://github.com/CristopherG19)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

**Sistema de Gestión de Remesas GASELAG** - Desarrollado con ❤️ en Laravel