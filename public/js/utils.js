/**
 * Utilidades JavaScript comunes para el sistema GASELAG
 */

// Función para formatear números con separadores de miles
function formatNumber(num) {
    return new Intl.NumberFormat('es-PE').format(num);
}

// Función para mostrar/ocultar loading spinner
function toggleLoading(element, show = true, text = 'Cargando...') {
    if (show) {
        element.disabled = true;
        element.dataset.originalText = element.innerHTML;
        element.innerHTML = `<i class="bi bi-hourglass-split"></i> ${text}`;
    } else {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText || element.innerHTML;
    }
}

// Función para mostrar alertas Bootstrap
function showAlert(message, type = 'info', container = null) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const targetContainer = container || document.querySelector('.container, .container-fluid');
    if (targetContainer) {
        targetContainer.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss después de 5 segundos
        setTimeout(() => {
            const alert = targetContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
}

// Función para validar formularios
function validateForm(formId, rules = {}) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const errors = [];
    
    // Limpiar errores previos
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    
    Object.keys(rules).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        const rule = rules[fieldName];
        
        if (!field) return;
        
        let fieldValid = true;
        let errorMessage = '';
        
        // Validación requerido
        if (rule.required && !field.value.trim()) {
            fieldValid = false;
            errorMessage = rule.requiredMessage || 'Este campo es requerido';
        }
        
        // Validación mínimo de caracteres
        if (fieldValid && rule.minLength && field.value.length < rule.minLength) {
            fieldValid = false;
            errorMessage = rule.minLengthMessage || `Mínimo ${rule.minLength} caracteres`;
        }
        
        // Validación formato email
        if (fieldValid && rule.email && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                fieldValid = false;
                errorMessage = rule.emailMessage || 'Formato de email inválido';
            }
        }
        
        if (!fieldValid) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Agregar mensaje de error
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = errorMessage;
            field.parentNode.appendChild(errorDiv);
            
            errors.push({ field: fieldName, message: errorMessage });
        }
    });
    
    return { isValid, errors };
}

// Función para manejar peticiones AJAX con manejo de errores
async function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    // Agregar CSRF token si es POST/PUT/DELETE
    if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(options.method?.toUpperCase())) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken;
        }
    }
    
    const finalOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, finalOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        } else {
            return await response.text();
        }
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

// Función para debounce (evitar múltiples ejecuciones)
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

// Función para actualizar contadores de caracteres
function setupCharacterCounters() {
    document.querySelectorAll('[data-max-length]').forEach(element => {
        const maxLength = parseInt(element.dataset.maxLength);
        const counterId = element.dataset.counter;
        const counter = counterId ? document.getElementById(counterId) : null;
        
        if (!counter) return;
        
        const updateCounter = () => {
            const currentLength = element.value.length;
            const remaining = maxLength - currentLength;
            
            counter.textContent = `${currentLength}/${maxLength} caracteres`;
            
            // Cambiar color según caracteres restantes
            counter.classList.remove('text-warning', 'text-danger');
            if (remaining < 100) {
                counter.classList.add('text-warning');
            }
            if (remaining < 0) {
                counter.classList.add('text-danger');
            }
        };
        
        // Configurar evento
        element.addEventListener('input', updateCounter);
        element.addEventListener('paste', () => setTimeout(updateCounter, 10));
        
        // Ejecutar una vez al cargar
        updateCounter();
    });
}

// Función para confirmar acciones destructivas
function confirmAction(message = '¿Estás seguro de realizar esta acción?') {
    return new Promise((resolve) => {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            // Usar modal de Bootstrap si está disponible
            const modalHtml = `
                <div class="modal fade" id="confirmModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmar Acción</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirmCancel">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="confirmOk">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Eliminar modal existente si hay uno
            const existingModal = document.getElementById('confirmModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            
            document.getElementById('confirmOk').addEventListener('click', () => {
                modal.hide();
                resolve(true);
            });
            
            document.getElementById('confirmCancel').addEventListener('click', () => {
                modal.hide();
                resolve(false);
            });
            
            modal.show();
        } else {
            // Fallback a confirm nativo
            resolve(confirm(message));
        }
    });
}

// Inicializar utilidades cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setupCharacterCounters();
    
    // Configurar tooltips de Bootstrap si están disponibles
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
    
    // Configurar popovers si están disponibles
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }
});