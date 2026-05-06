<?php
/**
 * HU-02: Modal de Términos de Uso
 * Componente reutilizable - Se muestra en la misma página
 */
?>

<div id="modalTerminos" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-description">
    <div class="modal-container">
        
        <div class="modal-header">
            <div>
                <h2 id="modal-title" class="modal-title">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                    Términos de Uso y Marco Legal
                </h2>
                <p id="modal-description" class="modal-subtitle">
                    Antes de continuar, debe conocer y aceptar los términos establecidos
                </p>
            </div>
            <button type="button" class="modal-close" onclick="cerrarModal()" aria-label="Cerrar modal" title="Cerrar">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        
        <div class="modal-body">
            
            <div class="legal-section">
                <h3 class="legal-heading">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    Información Importante
                </h3>
                <p class="legal-text">
                    El Sistema de Gestión PQRS es una plataforma oficial para la recepción, 
                    tramite y seguimiento de Peticiones, Quejas, Reclamos, Sugerencias y Denuncias. 
                    Al utilizar este servicio, usted acepta cumplir con las normas y procedimientos 
                    establecidos en la legislación colombiana vigente.
                </p>
            </div>
            
            <div class="legal-section">
                <h3 class="legal-heading">
                    <i class="bi bi-book" aria-hidden="true"></i>
                    Leyes Aplicables
                </h3>
                
                <div class="ley-item">
                    <div class="ley-title">
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        Ley 1755 de 2015
                    </div>
                    <div class="ley-description">
                        Por medio de la cual se regula el derecho fundamental de petición y 
                        se sustituyen unas disposiciones de la Ley 1437 de 2011.
                    </div>
                </div>
                
                <div class="ley-item">
                    <div class="ley-title">
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        Ley 1437 de 2011
                    </div>
                    <div class="ley-description">
                        Código de Procedimiento Administrativo y de lo Contencioso Administrativo.
                    </div>
                </div>
                
                <div class="ley-item">
                    <div class="ley-title">
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        Ley 1474 de 2011
                    </div>
                    <div class="ley-description">
                        Ley Anticorrupción. Establece medidas para fortalecer los mecanismos 
                        de prevención, investigación y sanción de actos de corrupción.
                    </div>
                </div>
                
                <div class="ley-item">
                    <div class="ley-title">
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        Ley 1581 de 2012
                    </div>
                    <div class="ley-description">
                        Por la cual se dictan disposiciones generales para la protección de 
                        datos personales.
                    </div>
                </div>
            </div>
            
            <div class="legal-section">
                <h3 class="legal-heading">
                    <i class="bi bi-person-check" aria-hidden="true"></i>
                    Sus Derechos
                </h3>
                <ul class="legal-list">
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>A presentar solicitudes de forma gratuita y recibir respuesta oportuna dentro de los términos legales.</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>A conocer el estado de trámite de su solicitud en cualquier momento.</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>A obtener copia de la respuesta y de los documentos relacionados con su PQRS.</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>A que se respete la confidencialidad de sus datos personales según la Ley 1581 de 2012.</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>A presentar quejas si no se cumplen los términos de respuesta establecidos.</span>
                    </li>
                </ul>
            </div>
            
            <div class="legal-section">
                <h3 class="legal-heading">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    Responsabilidades
                </h3>
                <ul class="legal-list">
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>Proporcionar información veraz, completa y actualizada.</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>No utilizar el sistema para fines distintos a los establecidos por la ley.</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>Guardar su código de radicado para futuras consultas.</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>Responder a requerimientos de información adicional cuando sea necesario.</span>
                    </li>
                </ul>
            </div>
            
        </div>
        
        <div class="modal-footer">
            <label class="checkbox-container" for="aceptoTerminos">
                <input type="checkbox" id="aceptoTerminos" name="aceptoTerminos" onchange="toggleBotonContinuar()">
                <span class="checkbox-label">
                    <strong>He leído y acepto</strong> los términos de uso, el marco legal aplicable 
                    y la política de tratamiento de datos personales. Declaro que la información 
                    que proporcionaré es veraz.
                </span>
            </label>
            
            <div class="modal-actions">
                <button type="button" class="btn-modal-secondary" onclick="cerrarModal()">
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    Cancelar
                </button>
                <button type="button" id="btnContinuar" class="btn-modal-primary" disabled onclick="continuarFormulario()">
                    <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                    Continuar al Formulario
                </button>
            </div>
        </div>
        
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modalTerminos').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    document.getElementById('modalTerminos').classList.remove('active');
    document.body.style.overflow = '';
}

function toggleBotonContinuar() {
    const checkbox = document.getElementById('aceptoTerminos');
    const boton = document.getElementById('btnContinuar');
    boton.disabled = !checkbox.checked;
}

function continuarFormulario() {
    const checkbox = document.getElementById('aceptoTerminos');
    if (!checkbox.checked) {
        alert('Debe aceptar los términos de uso para continuar.');
        return;
    }
    // Ocultar modal y mostrar formulario en la misma página
    cerrarModal();
    // Redirigir a tipos.php en la misma pestaña
    window.location.href = 'pqrs/tipos.php';
}

// Cerrar al presionar Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModal();
    }
});

// Cerrar al hacer clic fuera del modal
document.getElementById('modalTerminos').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});
</script>