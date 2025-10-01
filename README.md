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

### **1. Clonar el Repositorio**
```bash
git clone https://github.com/CristopherG19/sistema-gaselag.git
cd sistema-gaselag
```

### **2. Instalar Dependencias**
```bash
# Dependencias PHP
composer install

# Dependencias Node.js (opcional para desarrollo)
npm install
```

### **3. Configurar Variables de Entorno**
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### **4. Configurar Base de Datos**
Editar el archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_login
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### **5. Ejecutar Migraciones**
```bash
# Crear tablas de la base de datos
php artisan migrate

# (Opcional) Poblar con datos de prueba
php artisan db:seed
```

### **6. Configurar Permisos**
```bash
# Dar permisos de escritura a storage y bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### **7. Iniciar Servidor de Desarrollo**
```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

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

## 🐛 Solución de Problemas

### **Problemas Comunes**

#### **Error de Permisos**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### **Error de Base de Datos**
```bash
# Verificar conexión
php artisan tinker
>>> DB::connection()->getPdo();

# Recrear migraciones
php artisan migrate:fresh
```

#### **Error de Memoria PHP**
```ini
# php.ini
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
```

## 📝 Changelog

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