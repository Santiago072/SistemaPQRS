<?php
/* HU-Detalle y Respuesta: Formulario para responder PQRS */

include __DIR__ . '/../layouts/verificar_sesion.php';
include __DIR__ . '/../layouts/funciones.php';

// Los datos de $pqrs ya están disponibles porque el AdminController los obtiene y requiere esta vista


$tipoLabels = [
    'peticion'   => 'Petición',
    'queja'      => 'Queja',
    'reclamo'    => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia'   => 'Denuncia',
];
$estadoLabels = [
    'PENDIENTE'  => 'Pendiente',
    'EN_PROCESO' => 'En Proceso',
    'RESUELTO'   => 'Resuelto',
    'RECHAZADO'  => 'Rechazado',
];

// Determinar si el ciudadano recibirá correo (para mostrar indicador en UI)
$tienCorreo = !empty($pqrs['correo_electronico']) && $pqrs['tipo_persona'] !== 'anonima';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder PQRS <?php echo htmlspecialchars($pqrs['codigo_radicado']); ?> - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<?php
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
$baseUrl = $isRailway ? '/' : '/PROYECTO_PQRS/';
?>
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">    <style>
        /* ── Estilos específicos de esta vista ── */
        .detalle-nav { margin-bottom: var(--space-6); }

        .btn-volver-detalle {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--color-gray-500);
            font-size: var(--font-size-sm);
            font-weight: 500;
            padding: var(--space-2) var(--space-4);
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-lg);
            background: var(--color-white);
            transition: all var(--transition-fast);
            text-decoration: none;
        }
        .btn-volver-detalle:hover { border-color: var(--color-primary); color: var(--color-primary); background: #eff6ff; }

        /* Alertas */
        .alerta {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-4) var(--space-5);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-5);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        .alerta i { font-size: 1.25rem; flex-shrink: 0; }
        .alerta-exito { background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #059669; color: #065f46; }
        .alerta-error { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--color-danger); color: #991b1b; }

        /* Grid principal */
        .responder-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-6);
        }
        @media (min-width: 1024px) {
            .responder-grid { grid-template-columns: 1fr 340px; }
        }

        /* Tarjetas */
        .detalle-card {
            background: var(--color-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-gray-100);
            overflow: hidden;
            margin-bottom: var(--space-5);
        }
        .detalle-card:last-child { margin-bottom: 0; }
        .detalle-card-header {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--color-gray-100);
            background: var(--color-gray-50);
        }
        .detalle-card-header h2 {
            font-size: var(--font-size-base);
            font-weight: 700;
            color: var(--color-gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        .detalle-card-header h2 i { color: var(--color-primary); }
        .detalle-card-body { padding: var(--space-5); }

        /* Textarea grande */
        .respuesta-textarea {
            min-height: 220px;
            font-family: inherit;
            resize: vertical;
        }

        /* Indicador de correo */
        .correo-indicator {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            font-size: var(--font-size-xs);
            font-weight: 500;
            margin-top: var(--space-3);
        }
        .correo-indicator.si  { background: #f0fdf4; border: 1px solid #bbf7d0; color: #065f46; }
        .correo-indicator.no  { background: var(--color-gray-50); border: 1px solid var(--color-gray-200); color: var(--color-gray-500); }
        .correo-indicator i   { font-size: 1rem; flex-shrink: 0; }

        /* Form actions */
        .form-actions-responder {
            display: flex;
            gap: var(--space-3);
            justify-content: flex-end;
            margin-top: var(--space-6);
            padding-top: var(--space-5);
            border-top: 1px solid var(--color-gray-100);
        }

        /* Info items sidebar */
        .info-item { display: flex; flex-direction: column; gap: 2px; padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-100); }
        .info-item:last-child { border-bottom: none; }
        .info-item label { font-size: var(--font-size-xs); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--color-gray-400); }
        .info-item span  { font-size: var(--font-size-sm); color: var(--color-gray-800); font-weight: 500; }
        .info-item code  { background: var(--color-gray-100); padding: 2px 6px; border-radius: var(--radius-sm); font-family: 'Courier New', monospace; font-size: var(--font-size-xs); color: var(--color-primary); font-weight: 700; }
        .info-divider    { border: none; border-top: 2px solid var(--color-gray-100); margin: var(--space-2) 0; }

        /* Badges de tipo */
        .tipo-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .tipo-peticion   { background: #dbeafe; color: #1e40af; }
        .tipo-queja      { background: #fee2e2; color: #991b1b; }
        .tipo-reclamo    { background: #fef3c7; color: #92400e; }
        .tipo-sugerencia { background: #d1fae5; color: #065f46; }
        .tipo-denuncia   { background: #ede9fe; color: #5b21b6; }

        /* Días restantes */
        .text-danger  { color: var(--color-danger); font-weight: 600; }
        .text-warning { color: var(--color-accent);  font-weight: 600; }

        /* Plantillas */
        .plantillas-lista  { display: flex; flex-direction: column; gap: var(--space-2); }
        .plantilla-btn {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-md);
            background: var(--color-white);
            color: var(--color-gray-600);
            font-size: var(--font-size-sm);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-align: left;
        }
        .plantilla-btn:hover { border-color: var(--color-primary); color: var(--color-primary); background: #eff6ff; }
        .plantilla-btn i { font-size: 1rem; flex-shrink: 0; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <section class="dashboard-section">
        <div class="container">

            <!-- Navegación -->
            <div class="detalle-nav">
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_ver&id=<?php echo $id; ?>" class="btn-volver-detalle">
                    <i class="bi bi-arrow-left"></i>
                    Volver al detalle
                </a>
            </div>

            <!-- Mensajes -->
            <?php if ($mensaje_exito): ?>
            <div class="alerta alerta-exito">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
            <?php endif; ?>

            <?php if ($mensaje_error): ?>
            <div class="alerta alerta-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo htmlspecialchars($mensaje_error); ?>
            </div>
            <?php endif; ?>

            <div class="responder-grid">

                <!-- ── Formulario de respuesta ── -->
                <div class="responder-main">
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-reply"></i> Responder Solicitud</h2>
                        </div>
                        <div class="detalle-card-body">
                            <form method="POST" action="<?php echo BASE_PATH; ?>index.php?ruta=admin/guardar_respuesta" class="responder-form">
                                <input type="hidden" name="pqrs_id" value="<?php echo htmlspecialchars($pqrs['id']); ?>">

                                <!-- Contenido -->
                                <div class="form-grupo">
                                    <label class="form-label">
                                        <i class="bi bi-chat-left-text" style="margin-right:4px;color:var(--color-primary)"></i>
                                        Contenido de la Respuesta
                                        <span class="requerido">*</span>
                                    </label>
                                    <textarea
                                        name="contenido"
                                        class="form-textarea respuesta-textarea"
                                        rows="10"
                                        placeholder="Escriba aquí la respuesta formal a la solicitud..."
                                        required
                                    ><?php echo htmlspecialchars($_POST['contenido'] ?? ''); ?></textarea>
                                    <p class="form-ayuda">Esta respuesta quedará registrada en el sistema como respuesta oficial.</p>
                                </div>

                                <!-- Cambio de estado -->
                                <div class="form-grupo">
                                    <label class="form-label">
                                        <i class="bi bi-flag" style="margin-right:4px;color:var(--color-primary)"></i>
                                        Cambiar Estado
                                    </label>
                                    <select name="nuevo_estado" class="form-select">
                                        <option value="">
                                            Mantener estado actual
                                            (<?php echo $estadoLabels[$pqrs['estado']] ?? $pqrs['estado']; ?>)
                                        </option>
                                        <?php if ($pqrs['estado'] === 'PENDIENTE'): ?>
                                            <option value="EN_PROCESO">Cambiar a: En Proceso</option>
                                        <?php endif; ?>
                                        <?php if (in_array($pqrs['estado'], ['PENDIENTE', 'EN_PROCESO'])): ?>
                                            <option value="RESUELTO">Cambiar a: Resuelto</option>
                                            <option value="RECHAZADO">Cambiar a: Rechazado</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Visibilidad -->
                                <div class="form-grupo">
                                    <label class="checkbox-container" style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;padding:.75rem;background:var(--color-gray-50);border-radius:var(--radius-md);border:1px solid var(--color-gray-200);">
                                        <input type="checkbox" name="es_visible_publico" value="1" checked
                                               style="width:18px;height:18px;margin-top:2px;accent-color:var(--color-primary);"
                                               onchange="actualizarIndicadorCorreo(this.checked)">
                                        <span class="checkbox-label">
                                            <strong>Hacer visible al ciudadano</strong><br>
                                            <small style="color:var(--color-gray-500)">
                                                Si está marcado, el ciudadano podrá ver esta respuesta en el portal de consulta.
                                            </small>
                                        </span>
                                    </label>

                                    <!-- Indicador de correo (Opción B) -->
                                    <?php if ($tienCorreo): ?>
                                    <div class="correo-indicator si" id="indicadorCorreo">
                                        <i class="bi bi-envelope-check-fill"></i>
                                        <span>
                                            Se enviará notificación automática por correo a
                                            <strong><?php echo htmlspecialchars($pqrs['correo_electronico']); ?></strong>
                                        </span>
                                    </div>
                                    <?php else: ?>
                                    <div class="correo-indicator no" id="indicadorCorreo">
                                        <i class="bi bi-envelope-slash"></i>
                                        <span>
                                            <?php echo $pqrs['tipo_persona'] === 'anonima'
                                                ? 'Solicitud anónima — no se enviará correo.'
                                                : 'El ciudadano no registró correo — no se enviará notificación.'; ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Acciones -->
                                <div class="form-actions-responder">
                                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_ver&id=<?php echo $id; ?>" class="btn-volver">
                                        <i class="bi bi-x-circle"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn-enviar">
                                        <i class="bi bi-send"></i>
                                        Enviar Respuesta
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div><!-- /responder-main -->

                <!-- ── Sidebar ── -->
                <div class="responder-sidebar">

                    <!-- Resumen -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-file-text"></i> Resumen</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="info-item">
                                <label>Código</label>
                                <span><code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code></span>
                            </div>
                            <div class="info-item">
                                <label>Tipo</label>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Estado Actual</label>
                                <span class="estado-tag estado-<?php echo strtolower(str_replace('_', '-', $pqrs['estado'])); ?>">
                                    <?php echo $estadoLabels[$pqrs['estado']] ?? $pqrs['estado']; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Solicitante</label>
                                <span><?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></span>
                            </div>
                            <?php if (!empty($pqrs['correo_electronico'])): ?>
                            <div class="info-item">
                                <label>Correo</label>
                                <span style="font-size:var(--font-size-xs);word-break:break-all;">
                                    <?php echo htmlspecialchars($pqrs['correo_electronico']); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <hr class="info-divider">
                            <div class="info-item">
                                <label>Asunto</label>
                                <span><?php echo htmlspecialchars($pqrs['asunto']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Plazos -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-clock-history"></i> Plazos</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="info-item">
                                <label>Fecha Radicación</label>
                                <span><?php echo date('d/m/Y', strtotime($pqrs['fecha_radicacion'])); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Fecha Vencimiento</label>
                                <span><?php echo $pqrs['fecha_vencimiento'] ? date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])) : 'N/A'; ?></span>
                            </div>
                            <?php
                            $dr = $pqrs['dias_restantes'];
                            if ($dr !== null && !in_array($pqrs['estado'], ['RESUELTO','RECHAZADO'])): ?>
                            <div class="info-item">
                                <label>Días Restantes</label>
                                <span class="<?php echo $dr < 0 ? 'text-danger' : ($dr <= 5 ? 'text-warning' : ''); ?>">
                                    <?php echo $dr < 0
                                        ? 'Vencida hace ' . abs($dr) . ' días'
                                        : $dr . ' días restantes'; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Plantillas -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-file-earmark-text"></i> Plantillas</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="plantillas-lista">
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('recibido')">
                                    <i class="bi bi-check2-circle"></i> Acuse de Recibido
                                </button>
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('proceso')">
                                    <i class="bi bi-arrow-repeat"></i> En Proceso
                                </button>
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('resuelto')">
                                    <i class="bi bi-check-circle"></i> Solicitud Resuelta
                                </button>
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('rechazado')">
                                    <i class="bi bi-x-circle"></i> Solicitud Rechazada
                                </button>
                            </div>
                        </div>
                    </div>

                </div><!-- /responder-sidebar -->

            </div><!-- /responder-grid -->
        </div>
    </section>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
    // ── Plantillas de respuesta ────────────────────────────────────
    const codigo = '<?php echo addslashes($pqrs['codigo_radicado']); ?>';
    const plantillas = {
        recibido: `Estimado(a) ciudadano(a),\nNos permitimos informarle que su solicitud con radicado ${codigo} ha sido recibida exitosamente en nuestra entidad.\nSu solicitud será atendida dentro de los términos legales establecidos.\nCordialmente,\nSistema PQRS`,
        proceso:  `Estimado(a) ciudadano(a),\nLe informamos que su solicitud con radicado ${codigo} se encuentra actualmente en proceso de gestión.\nNuestro equipo está trabajando para darle respuesta en el menor tiempo posible.\nCordialmente,\nSistema PQRS`,
        resuelto: `Estimado(a) ciudadano(a),\nNos complace informarle que su solicitud con radicado ${codigo} ha sido resuelta satisfactoriamente.\n[Incluir aquí los detalles de la resolución]\nSi tiene alguna pregunta adicional, no dude en contactarnos.\nCordialmente,\nSistema PQRS`,
        rechazado:`Estimado(a) ciudadano(a),\nLamentamos informarle que su solicitud con radicado ${codigo} no ha podido ser tramitada por las siguientes razones:\n[Especificar las razones del rechazo]\nSi considera que esta decisión es incorrecta, puede presentar una nueva solicitud con la documentación adecuada.\nCordialmente,\nSistema PQRS`
    };

    function insertarPlantilla(tipo) {
        const textarea = document.querySelector('.respuesta-textarea');
        if (!textarea || !plantillas[tipo]) return;
        if (textarea.value && !confirm('¿Desea reemplazar el contenido actual con la plantilla?')) return;
        textarea.value = plantillas[tipo];
        textarea.focus();
    }

    // ── Indicador de correo según visibilidad ──────────────────────
    const tienCorreo = <?php echo $tienCorreo ? 'true' : 'false'; ?>;

    function actualizarIndicadorCorreo(esVisible) {
        const indicador = document.getElementById('indicadorCorreo');
        if (!indicador || !tienCorreo) return;

        if (esVisible) {
            indicador.className = 'correo-indicator si';
            indicador.innerHTML = `
                <i class="bi bi-envelope-check-fill"></i>
                <span>Se enviará notificación automática por correo al ciudadano</span>`;
        } else {
            indicador.className = 'correo-indicator no';
            indicador.innerHTML = `
                <i class="bi bi-envelope-slash"></i>
                <span>Respuesta interna — no se enviará correo al ciudadano</span>`;
        }
    }
    </script>

</body>
</html>
