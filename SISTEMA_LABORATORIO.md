# Sistema de Laboratorio de Ensayos de Medidores de Agua

## Descripción General

El Sistema de Laboratorio es un módulo especializado para técnicos de laboratorio que permite gestionar ensayos de medidores de agua según la norma NMP 005:2018. Está optimizado para tablets y ofrece una interfaz táctil intuitiva.

## Características Principales

### ✅ Funcionalidades Implementadas

1. **Dashboard Principal**
   - Vista general del estado de todos los bancos de ensayo
   - Estadísticas en tiempo real de ensayos
   - Capacidad ocupada/disponible de cada banco
   - Accesos rápidos a funciones principales

2. **Gestión de Ensayos**
   - Crear nuevos ensayos con información completa del medidor
   - Seguimiento del estado: pendiente → en proceso → completado
   - Cálculo automático de errores según NMP 005:2018
   - Registro de condiciones ambientales

3. **Bancos de Ensayo**
   - Gestión de 3 bancos predefinidos con diferentes capacidades
   - Control de ocupación (máximo 15 medidores por banco principal)
   - Asignación automática de bancos disponibles

4. **Cumplimiento de Norma NMP 005:2018**
   - Ensayos en tres caudales: Q1, Q2, Q3
   - Límites de error automáticos: ±5% (Q1), ±2% (Q2, Q3)
   - Cálculo automático de resultado (aprobado/rechazado)
   - Registro de condiciones ambientales

5. **Interface Optimizada para Tablets**
   - Botones de tamaño táctil (mínimo 44px)
   - Formularios adaptados para tablets
   - Navegación simplificada
   - Auto-actualización cada 30 segundos

## Estructura de la Base de Datos

### Tabla `bancos_ensayo`
- **Propósito**: Gestión de bancos de ensayo
- **Capacidades**: 15, 10, 8 medidores por banco
- **Estados**: activo, mantenimiento, inactivo
- **Control de calibración**: fechas de última y próxima calibración

### Tabla `ensayos`
- **Información del medidor**: número, marca, modelo, calibre, clase
- **Datos técnicos**: caudales Q1/Q2/Q3, volúmenes, errores calculados
- **Condiciones ambientales**: temperatura, presión, humedad
- **Control de tiempos**: inicio, finalización, duración
- **Certificación**: número de certificado para aprobados

## Flujo de Trabajo

### 1. Crear Nuevo Ensayo
```
Técnico accede → Laboratorio → Nuevo Ensayo
↓
Complete información del medidor
↓
Selecciona banco disponible
↓
Sistema crea ensayo en estado "pendiente"
```

### 2. Ejecutar Ensayo
```
Ensayo creado → Iniciar Ensayo (estado: "en_proceso")
↓
Registrar datos de Q1, Q2, Q3
↓
Sistema calcula errores automáticamente
↓
Completar condiciones ambientales
↓
Finalizar ensayo
```

### 3. Resultado Final
```
Ensayo completado → Sistema evalúa errores vs NMP 005:2018
↓
Si cumple límites → "APROBADO" + Certificado
↓
Si no cumple → "RECHAZADO"
```

## Roles y Permisos

### Técnico de Laboratorio (`tecnico_laboratorio`)
- Acceso completo al sistema de laboratorio
- Crear y gestionar sus propios ensayos
- Ver todos los bancos de ensayo
- Generar certificados

### Administrador (`admin`)
- Todos los permisos de técnico
- Ver ensayos de todos los técnicos
- Gestión avanzada de bancos
- Estadísticas globales

## Usuarios de Prueba Creados

```
Email: tecnico.lab@gaselag.com
Password: tecnico123
Rol: Técnico de Laboratorio

Email: maria.especialista@gaselag.com  
Password: especialista123
Rol: Técnico de Laboratorio
```

## Bancos de Ensayo Predefinidos

1. **Banco Principal A**
   - Capacidad: 15 medidores
   - Ubicación: Laboratorio Principal - Módulo A
   - Estado: Activo

2. **Banco Secundario B**
   - Capacidad: 10 medidores
   - Ubicación: Laboratorio Principal - Módulo B
   - Estado: Activo

3. **Banco Calibres Grandes C**
   - Capacidad: 8 medidores
   - Ubicación: Laboratorio Especial - Área C
   - Estado: Activo

## Rutas Principales

```
/laboratorio                    - Dashboard principal
/laboratorio/nuevo-ensayo       - Crear nuevo ensayo
/laboratorio/ensayo/{id}        - Ver/editar ensayo específico
/laboratorio/ensayos            - Lista de todos los ensayos
/laboratorio/bancos             - Gestión de bancos de ensayo
/laboratorio/ensayo/{id}/certificado - Generar certificado PDF
```

## Instalación y Configuración

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

### 2. Poblar Datos de Prueba
```bash
php artisan db:seed --class=BancoEnsayoSeeder
```

### 3. Crear Usuario Técnico (si es necesario)
```php
Usuario::create([
    'nombre' => 'Técnico',
    'apellidos' => 'Laboratorio',
    'correo' => 'tecnico@empresa.com',
    'password' => Hash::make('password'),
    'rol' => 'tecnico_laboratorio',
    'activo' => true
]);
```

## Características Técnicas

### Cálculos Automáticos
- **Error (%)** = ((Volumen_Medidor - Volumen_Ensayo) / Volumen_Ensayo) × 100
- **Precisión**: 4 decimales
- **Validación automática** contra límites NMP 005:2018

### Optimización para Tablets
- Formularios con campos de tamaño táctil
- Navegación simplificada
- Auto-guardado de datos
- Actualizaciones en tiempo real
- Interface responsive

### Seguridad
- Autenticación requerida
- Control de permisos por rol
- Validación de datos del servidor
- Protección CSRF en todos los formularios

## Próximas Mejoras Sugeridas

1. **Generación de PDF**: Certificados completos con logotipo y sellos
2. **Reportes**: Estadísticas mensuales y anuales
3. **Notificaciones**: Alertas para calibraciones vencidas
4. **API REST**: Para integración con otros sistemas
5. **Backup**: Respaldo automático de datos críticos
6. **Mantenimiento**: Gestión de mantenimiento de bancos
7. **Trazabilidad**: Historial completo de cambios

## Soporte Técnico

Para problemas o consultas:
- Revisar logs en `storage/logs/laravel.log`
- Verificar permisos de usuario en la base de datos
- Validar que los bancos estén en estado "activo"
- Confirmar que las migraciones se ejecutaron correctamente

---

**Nota**: Este sistema cumple con los estándares de la norma NMP 005:2018 para ensayos de medidores de agua y está optimizado para uso en tablets en entornos de laboratorio.
