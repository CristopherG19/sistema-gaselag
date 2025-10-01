# Guía de Contribución

¡Gracias por tu interés en contribuir al Sistema de Gestión de Remesas GASELAG! 

## 🚀 Cómo Contribuir

### **1. Fork del Repositorio**
- Haz fork del repositorio en GitHub
- Clona tu fork localmente:
```bash
git clone https://github.com/TU_USUARIO/sistema-gaselag.git
cd sistema-gaselag
```

### **2. Configurar el Entorno**
```bash
# Instalar dependencias
composer install
npm install

# Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos
php artisan migrate
```

### **3. Crear una Rama**
```bash
git checkout -b feature/nombre-de-la-funcionalidad
# o
git checkout -b fix/descripcion-del-bug
```

### **4. Hacer Cambios**
- Realiza tus cambios siguiendo las convenciones del proyecto
- Asegúrate de que el código funcione correctamente
- Agrega tests si es necesario

### **5. Commit y Push**
```bash
git add .
git commit -m "feat: agregar nueva funcionalidad"
git push origin feature/nombre-de-la-funcionalidad
```

### **6. Crear Pull Request**
- Ve a GitHub y crea un Pull Request
- Describe claramente los cambios realizados
- Espera la revisión del código

## 📋 Estándares de Código

### **PHP/Laravel**
- Seguir PSR-12 para estilo de código
- Usar convenciones de Laravel
- Documentar funciones complejas
- Escribir código en español (comentarios y variables)

### **JavaScript/CSS**
- Usar ES6+ para JavaScript
- Seguir convenciones de Bootstrap
- Comentar código complejo

### **Base de Datos**
- Usar migraciones para cambios de esquema
- Nombrar tablas en singular
- Usar snake_case para nombres de columnas

## 🧪 Testing

### **Antes de Enviar PR**
```bash
# Ejecutar tests
php artisan test

# Verificar sintaxis
php -l archivo.php

# Verificar estilo de código
./vendor/bin/pint --test
```

## 📝 Convenciones de Commits

Usar [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: nueva funcionalidad
fix: corrección de bug
docs: actualización de documentación
style: cambios de formato
refactor: refactorización de código
test: agregar o modificar tests
chore: tareas de mantenimiento
```

### **Ejemplos:**
```bash
git commit -m "feat: agregar filtro por fecha en vista de remesas"
git commit -m "fix: corregir error de paginación en lista de registros"
git commit -m "docs: actualizar README con instrucciones de instalación"
```

## 🐛 Reportar Bugs

### **Usar el Template de Bug Report**
- Usar el template de GitHub Issues
- Incluir pasos para reproducir
- Agregar capturas de pantalla si es necesario
- Especificar versión de PHP, Laravel, etc.

### **Información Requerida**
- Descripción clara del problema
- Pasos para reproducir
- Comportamiento esperado vs actual
- Información del sistema
- Logs de error si aplica

## 💡 Solicitar Funcionalidades

### **Usar el Template de Feature Request**
- Describir la funcionalidad deseada
- Explicar el caso de uso
- Proponer implementación si es posible
- Considerar impacto en el sistema existente

## 📚 Documentación

### **Actualizar Documentación**
- README.md para cambios importantes
- Comentarios en código para funciones complejas
- Documentar APIs si se crean
- Actualizar changelog

## 🔍 Proceso de Revisión

### **Criterios de Aceptación**
- ✅ Código funciona correctamente
- ✅ Sigue estándares del proyecto
- ✅ No rompe funcionalidad existente
- ✅ Incluye documentación necesaria
- ✅ Tests pasan (si aplica)

### **Feedback**
- Los maintainers revisarán el código
- Se pueden solicitar cambios
- Se responderá en un plazo razonable

## 🤝 Código de Conducta

### **Respeto Mutuo**
- Ser respetuoso en todas las interacciones
- Construir sobre las ideas de otros
- Aceptar críticas constructivas
- Ayudar a otros contribuidores

### **Comunicación**
- Usar español en issues y PRs
- Ser claro y específico
- Preguntar si algo no está claro
- Mantener discusiones enfocadas

## 📞 Contacto

### **Dudas o Preguntas**
- Abrir un issue en GitHub
- Contactar al maintainer: cgutierrez@gaselag.com
- Usar las discusiones del repositorio

---

¡Gracias por contribuir al proyecto! 🎉
