<?php
/**
 * HU-03: Tipos de Solicitud
 * Selección visual del tipo de PQRS
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Tipo de Solicitud - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="tipos-page">

    <!-- Header simple -->
    <header style="background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:1rem 0;margin-bottom:2rem;">
        <div style="max-width:1200px;margin:0 auto;padding:0 1rem;display:flex;align-items:center;gap:0.5rem;">
            <i class="bi bi-clipboard-data" style="color:#1e40af;font-size:1.5rem;"></i>
            <span style="font-weight:700;color:#1e40af;font-size:1.25rem;">Sistema PQRS</span>
        </div>
    </header>

    <div class="container" style="max-width:1200px;margin:0 auto;">
        
        <!-- Barra de progreso -->
        <div class="tipos-progress">
            <div class="progress-bar">
                <div class="progress-step">
                    <span class="progress-step-num"><i class="bi bi-check-lg"></i></span>
                    <span class="progress-step-label">Términos</span>
                </div>
                <div class="progress-step">
                    <span class="progress-step-num">2</span>
                    <span class="progress-step-label">Tipo PQRS</span>
                </div>
                <div class="progress-step">
                    <span class="progress-step-num inactive">3</span>
                    <span class="progress-step-label">Formulario</span>
                </div>
                <div class="progress-step">
                    <span class="progress-step-num inactive">4</span>
                    <span class="progress-step-label">Confirmación</span>
                </div>
            </div>
        </div>

        <!-- Título -->
        <div class="tipos-header">
            <h1 class="tipos-title">¿Qué tipo de solicitud desea radicar?</h1>
            <p class="tipos-subtitle">Seleccione una opción para continuar</p>
        </div>

        <!-- Grid de tarjetas -->
        <form id="formTipo" action="formulario.php" method="GET">
            
            <div class="tipos-grid">

                <!-- Petición -->
                <article class="tipo-card tipo-peticion" data-tipo="peticion" onclick="seleccionarTipo('peticion')">
                    <div class="tipo-card-header">
                        <div class="tipo-icon">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <h2 class="tipo-card-titulo">Petición</h2>
                        <p class="tipo-card-desc">Derecho fundamental de presentar solicitudes respetuosas ante autoridades por motivos de interés general o particular</p>
                    </div>
                </article>

                <!-- Queja -->
                <article class="tipo-card tipo-queja" data-tipo="queja" onclick="seleccionarTipo('queja')">
                    <div class="tipo-card-header">
                        <div class="tipo-icon">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                        <h2 class="tipo-card-titulo">Queja</h2>
                        <p class="tipo-card-desc">Manifestación de inconformidad por la conducta de un servidor público o particular en el ejercicio de sus funciones</p>
                    </div>
                </article>

                <!-- Reclamo -->
                <article class="tipo-card tipo-reclamo" data-tipo="reclamo" onclick="seleccionarTipo('reclamo')">
                    <div class="tipo-card-header">
                        <div class="tipo-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h2 class="tipo-card-titulo">Reclamo</h2>
                        <p class="tipo-card-desc">Derecho del usuario para exigir el cumplimiento de derechos vulnerados o reclamar por prestación inadecuada de un servicio</p>
                    </div>
                </article>

                <!-- Sugerencia -->
                <article class="tipo-card tipo-sugerencia" data-tipo="sugerencia" onclick="seleccionarTipo('sugerencia')">
                    <div class="tipo-card-header">
                        <div class="tipo-icon">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <h2 class="tipo-card-titulo">Sugerencia</h2>
                        <p class="tipo-card-desc">Propuesta constructiva para mejorar los procesos, servicios o procedimientos de la entidad, orientada al beneficio colectivo</p>
                    </div>
                </article>

                <!-- Denuncia -->
                <article class="tipo-card tipo-denuncia" data-tipo="denuncia" onclick="seleccionarTipo('denuncia')">
                    <div class="tipo-card-header">
                        <div class="tipo-icon">
                            <i class="bi bi-megaphone"></i>
                        </div>
                        <h2 class="tipo-card-titulo">Denuncia</h2>
                        <p class="tipo-card-desc">Comunicación de posibles irregularidades, actos de corrupción o violaciones a la normatividad por parte de servidores públicos</p>
                    </div>
                </article>

            </div>

            <!-- Campo oculto para enviar -->
            <input type="hidden" id="tipo_pqrs" name="tipo_pqrs" value="">
            
            <!-- Botón continuar -->
            <div class="tipos-actions">
                <!-- Botón volver -->
                <a href="../index.php" class="btn-volver">
                    <i class="bi bi-arrow-left"></i>
                    Volver al inicio
                </a>
                <button type="submit" id="btnContinuar" class="btn-continuar" disabled>
                    <span>Continuar al Formulario</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>
            </div>

        </form>

    </div>

    <script>
    let tipoSeleccionado = null;

    function seleccionarTipo(tipo) {
        // Desmarcar todas las tarjetas
        document.querySelectorAll('.tipo-card').forEach(card => {
            card.classList.remove('seleccionada');
        });
        
        // Marcar tarjeta seleccionada
        const tarjeta = document.querySelector(`.tipo-card[data-tipo="${tipo}"]`);
        tarjeta.classList.add('seleccionada');
        
        // Guardar selección
        tipoSeleccionado = tipo;
        document.getElementById('tipo_pqrs').value = tipo;
        
        // Habilitar botón
        document.getElementById('btnContinuar').disabled = false;
    }

    // Validar antes de enviar
    document.getElementById('formTipo').addEventListener('submit', function(e) {
        if (!tipoSeleccionado) {
            e.preventDefault();
            alert('Debe seleccionar un tipo de solicitud para continuar.');
            return false;
        }
    });
    </script>

</body>
</html>