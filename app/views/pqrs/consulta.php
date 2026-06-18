<?php
/**
 * HU-06: Consulta de Estado de PQRS
 * Permite consultar por código de radicado o correo electrónico
 */
<?php
/* HU-06: Consulta de Estado de PQRS */

$estados = [
    'PENDIENTE'   => ['texto' => 'Pendiente',   'color' => '#d97706', 'bg' => '#fef3c7', 'icon' => 'bi-clock'],
    'EN_PROCESO'  => ['texto' => 'En Proceso',  'color' => '#1e40af', 'bg' => '#dbeafe', 'icon' => 'bi-arrow-repeat'],
    'RESUELTO'    => ['texto' => 'Resuelto',    'color' => '#059669', 'bg' => '#d1fae5', 'icon' => 'bi-check-circle-fill'],
    'RECHAZADO'   => ['texto' => 'Rechazado',   'color' => '#dc2626', 'bg' => '#fee2e2', 'icon' => 'bi-x-circle-fill'],
];

$nombresTipos = [
    'peticion'   => 'Petición',
    'queja'      => 'Queja',
    'reclamo'    => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia'   => 'Denuncia',
];

// ── Helper: calcular % de progreso según estado ───────────────────────────────
function progresoPorEstado(string $estado): int {
    return match($estado) {
        'PENDIENTE'  => 25,
        'EN_PROCESO' => 60,
        'RESUELTO'   => 100,
        'RECHAZADO'  => 100,
        default      => 0,
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Consulta el estado de tu solicitud PQRS por código de radicado o correo electrónico.">
    <title>Consultar Estado - Sistema PQRS</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">
    <style>
        /* ── Modal de Archivo Adjunto ── */
        .modal-adjunto-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-adjunto-overlay.activo { display: flex; }
        .modal-adjunto-box {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 25px 60px rgba(0,0,0,.4);
            width: 100%;
            max-width: 860px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            animation: slideUpModal .25s ease;
            overflow: hidden;
        }
        @keyframes slideUpModal {
            from { transform: translateY(24px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }
        .modal-adjunto-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .9rem 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 1rem 1rem 0 0;
            gap: 1rem;
        }
        .modal-adjunto-header h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: .5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .modal-adjunto-header h3 i { color: #1e40af; flex-shrink: 0; }
        .modal-header-acciones { display: flex; gap: .5rem; flex-shrink: 0; }
        .modal-btn-descargar {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #1e40af;
            color: #fff;
            border: none;
            border-radius: .5rem;
            padding: .45rem .9rem;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s;
        }
        .modal-btn-descargar:hover { background: #1e3a8a; color: #fff; }
        .modal-btn-cerrar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            color: #64748b;
            font-size: 1rem;
            cursor: pointer;
            transition: all .2s;
        }
        .modal-btn-cerrar:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }
        .modal-adjunto-body {
            flex: 1;
            overflow: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: #0f172a;
            min-height: 300px;
        }
        .modal-adjunto-body img {
            max-width: 100%;
            max-height: 70vh;
            border-radius: .5rem;
            object-fit: contain;
            box-shadow: 0 8px 32px rgba(0,0,0,.5);
        }
        .modal-adjunto-body iframe {
            width: 100%;
            height: 70vh;
            border: none;
        }
        .modal-adjunto-descarga {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 2.5rem 1rem;
            color: #94a3b8;
            text-align: center;
        }
        .modal-adjunto-descarga i { font-size: 4rem; color: #475569; }
        .modal-adjunto-descarga p { margin: 0; font-size: .9rem; }
    </style>
</head>
<body class="consulta-page">

    <!-- ── HEADER ─────────────────────────────────────────────────────────── -->
    <header class="header">
        <div class="container header-container">
            <a href="<?php echo BASE_PATH; ?>index.php" class="logo" aria-label="Inicio - Sistema PQRS">
                <span class="logo-icon"><i class="bi bi-clipboard-data"></i></span>
                <span>Sistema PQRS</span>
            </a>
            <nav class="nav-admin">
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/login" class="btn btn-outline">
                    <i class="bi bi-shield-lock"></i>
                    <span>Administrador</span>
                </a>
            </nav>
        </div>
    </header>

    <main>

        <!-- ── HERO ───────────────────────────────────────────────────────── -->
        <section class="consulta-hero">
            <div class="container consulta-hero-content">
                <div class="consulta-hero-tag">
                    <i class="bi bi-search"></i>
                    <span>Seguimiento de solicitudes</span>
                </div>
                <h1>Consulta el estado de tu PQRS</h1>
                <p>Ingresa tu código de radicado o correo electrónico para ver el estado actualizado de tu solicitud.</p>
            </div>
        </section>

        <!-- ── TARJETA DE BÚSQUEDA ────────────────────────────────────────── -->
        <div class="container" style="padding:0 var(--space-4);">
            <div class="busqueda-card">

                <!-- Tabs -->
                <div class="busqueda-tabs" role="tablist">
                    <button class="tab-btn activo" id="tab-codigo" role="tab"
                            aria-selected="true" aria-controls="panel-codigo"
                            onclick="cambiarTab('codigo')">
                        <i class="bi bi-hash"></i>
                        Código de Radicado
                    </button>
                    <button class="tab-btn" id="tab-correo" role="tab"
                            aria-selected="false" aria-controls="panel-correo"
                            onclick="cambiarTab('correo')">
                        <i class="bi bi-envelope"></i>
                        Correo Electrónico
                    </button>
                </div>

                <!-- Panel: código -->
                <div id="panel-codigo" class="busqueda-panel activo" role="tabpanel">
                    <p class="busqueda-panel-desc">
                        Ingresa el código único que recibiste al radicar tu solicitud. El formato es <strong>PQRS-AAAA-MM-NNN</strong>.
                    </p>
                    <form method="POST" action="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/consulta">
                        <div class="busqueda-input-wrap">
                            <input type="text" name="codigo" class="busqueda-input"
                                   placeholder="Ej: PQRS-2026-05-001"
                                   value="<?php echo htmlspecialchars($_POST['codigo'] ?? $_GET['codigo'] ?? ''); ?>"
                                   autocomplete="off" spellcheck="false"
                                   aria-label="Código de radicado">
                            <button type="submit" class="busqueda-btn">
                                <i class="bi bi-search"></i>
                                Consultar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Panel: correo -->
                <div id="panel-correo" class="busqueda-panel" role="tabpanel">
                    <p class="busqueda-panel-desc">
                        Ingresa el correo con el que registraste tu solicitud para ver todas tus PQRS asociadas.
                    </p>
                    <form method="POST" action="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/consulta">
                        <div class="busqueda-input-wrap">
                            <input type="email" name="correo" class="busqueda-input"
                                   placeholder="Ej: correo@ejemplo.com"
                                   value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>"
                                   autocomplete="email"
                                   aria-label="Correo electrónico">
                            <button type="submit" class="busqueda-btn">
                                <i class="bi bi-search"></i>
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>

            </div><!-- /busqueda-card -->
        </div>

        <!-- ── RESULTADOS ─────────────────────────────────────────────────── -->
        <section class="resultados-section">

            <?php if ($error): ?>
            <!-- Error -->
            <div class="alerta-error" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <p><?php echo $error; ?></p>
            </div>

            <?php elseif (!empty($resultados)): ?>
            <!-- Cabecera resultados -->
            <div class="resultados-header">
                <div>
                    <p class="resultados-titulo">
                        <?php if ($tipoBusqueda === 'correo'): ?>
                            Solicitudes asociadas a <em><?php echo htmlspecialchars($busqueda); ?></em>
                        <?php else: ?>
                            Resultado de la consulta
                        <?php endif; ?>
                    </p>
                </div>
                <?php if (count($resultados) > 1): ?>
                <span class="resultados-count"><?php echo count($resultados); ?> solicitudes</span>
                <?php endif; ?>
            </div>

            <!-- Tarjetas de resultado -->
            <?php foreach ($resultados as $pqrs):
                $estadoKey   = $pqrs['estado'] ?? 'PENDIENTE';
                $estadoInfo  = $estados[$estadoKey] ?? $estados['PENDIENTE'];
                $estadoSlug  = strtolower($estadoKey);
                $progreso    = progresoPorEstado($estadoKey);
                $tipoLabel   = $nombresTipos[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']);
                $fechaRad    = date('d/m/Y H:i', strtotime($pqrs['fecha_radicacion']));
                $fechaVenc   = $pqrs['fecha_vencimiento'] ? date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])) : '—';
                $nombre      = $pqrs['nombre_completo'] ?: ($pqrs['nombre_representante'] ?: null);
                $hayRespuesta = !empty(trim($pqrs['respuesta_administrador'] ?? ''));
            ?>
            <article class="resultado-card" aria-label="Solicitud <?php echo htmlspecialchars($pqrs['codigo_radicado']); ?>">

                <!-- Barra de color superior -->
                <div class="resultado-topbar <?php echo $estadoSlug; ?>"></div>

                <!-- Cabecera -->
                <div class="resultado-head">
                    <div>
                        <div class="resultado-codigo">
                            <i class="bi bi-hash" style="opacity:.5;font-size:.9em;"></i>
                            <?php echo htmlspecialchars($pqrs['codigo_radicado']); ?>
                        </div>
                        <div class="resultado-fecha">
                            <i class="bi bi-calendar3" style="margin-right:4px;"></i>
                            Radicado el <?php echo $fechaRad; ?>
                        </div>
                    </div>
                    <span class="estado-badge"
                          style="background:<?php echo $estadoInfo['bg']; ?>;color:<?php echo $estadoInfo['color']; ?>;">
                        <i class="bi <?php echo $estadoInfo['icon']; ?>"></i>
                        <?php echo $estadoInfo['texto']; ?>
                    </span>
                </div>

                <!-- Detalles -->
                <div class="resultado-body">
                    <div class="resultado-grid">
                        <div class="info-item">
                            <div class="info-label">Tipo de solicitud</div>
                            <div class="info-value"><?php echo $tipoLabel; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Asunto</div>
                            <div class="info-value"><?php echo htmlspecialchars($pqrs['asunto']); ?></div>
                        </div>
                        <?php if ($nombre): ?>
                        <div class="info-item">
                            <div class="info-label">Solicitante</div>
                            <div class="info-value"><?php echo htmlspecialchars($nombre); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <div class="info-label">Fecha límite de respuesta</div>
                            <div class="info-value">
                                <?php echo $fechaVenc; ?>
                                <?php if ($pqrs['fecha_vencimiento'] && $estadoKey === 'PENDIENTE' || $estadoKey === 'EN_PROCESO'):
                                    $dias = (new DateTime())->diff(new DateTime($pqrs['fecha_vencimiento']))->days;
                                    $vencido = new DateTime() > new DateTime($pqrs['fecha_vencimiento']);
                                    if ($vencido): ?>
                                        <span style="color:var(--color-danger);font-size:.75rem;margin-left:4px;">(Vencido)</span>
                                    <?php else: ?>
                                        <span style="color:var(--color-gray-400);font-size:.75rem;margin-left:4px;">(<?php echo $dias; ?> días restantes)</span>
                                    <?php endif;
                                endif; ?>
                            </div>
                        </div>
                        <div class="info-item" style="grid-column:1/-1;">
                            <div class="info-label">Descripción</div>
                            <div class="info-value" style="color:var(--color-gray-600);line-height:1.6;">
                                <?php echo nl2br(htmlspecialchars($pqrs['descripcion'])); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra de progreso -->
                <div class="progreso-wrap">
                    <div class="progreso-label">
                        <span>Progreso de la solicitud</span>
                        <span><?php echo $progreso; ?>%</span>
                    </div>
                    <div class="progreso-track" role="progressbar"
                         aria-valuenow="<?php echo $progreso; ?>"
                         aria-valuemin="0" aria-valuemax="100">
                        <div class="progreso-fill <?php echo $estadoSlug; ?>"
                             style="width:<?php echo $progreso; ?>%;"></div>
                    </div>
                    <div class="hitos">
                        <?php
                        $hitos = [
                            ['label' => 'Radicada',   'umbral' => 0],
                            ['label' => 'En proceso', 'umbral' => 50],
                            ['label' => 'Resuelta',   'umbral' => 99],
                        ];
                        foreach ($hitos as $h): ?>
                        <div class="hito <?php echo ($progreso >= $h['umbral']) ? 'alcanzado' : ''; ?>">
                            <div class="hito-dot"></div>
                            <span><?php echo $h['label']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Respuesta del administrador -->
                <?php if ($hayRespuesta): ?>
                <div class="respuesta-admin">
                    <div class="respuesta-admin-titulo">
                        <i class="bi bi-chat-left-text-fill"></i>
                        Respuesta del Administrador
                    </div>
                    <div class="respuesta-admin-texto">
                        <?php echo nl2br(htmlspecialchars($pqrs['respuesta_administrador'])); ?>
                    </div>
                    <?php if (!empty($pqrs['fecha_respuesta'])): ?>
                    <div class="respuesta-admin-fecha">
                        <i class="bi bi-clock" style="margin-right:4px;"></i>
                        Respondido el <?php echo date('d/m/Y H:i', strtotime($pqrs['fecha_respuesta'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="sin-respuesta">
                    <i class="bi bi-hourglass-split"></i>
                    <span>Aún no hay respuesta del administrador. Le notificaremos cuando su solicitud sea atendida.</span>
                </div>
                <?php endif; ?>

                <!-- Adjunto -->
                <?php if (!empty($pqrs['archivo_adjunto'])):
                    $nombreArch = basename($pqrs['archivo_adjunto']);
                    $urlArch    = BASE_PATH . 'uploads/' . rawurlencode($nombreArch);
                    $extArch    = strtolower(pathinfo($nombreArch, PATHINFO_EXTENSION));
                    $iconArch   = match(true) {
                        in_array($extArch, ['jpg','jpeg','png','gif','webp']) => 'bi-file-earmark-image',
                        $extArch === 'pdf' => 'bi-file-earmark-pdf',
                        in_array($extArch, ['doc','docx']) => 'bi-file-earmark-word',
                        default => 'bi-file-earmark'
                    };
                ?>
                <button type="button"
                    class="adjunto-link"
                    onclick="abrirModalAdjunto('<?php echo htmlspecialchars($urlArch, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($nombreArch, ENT_QUOTES); ?>', '<?php echo $extArch; ?>')"
                    style="display:inline-flex;align-items:center;gap:.4rem;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:.5rem;padding:.45rem .9rem;font-size:.875rem;font-weight:500;cursor:pointer;transition:all .2s;margin-top:.25rem;"
                    onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                    <i class="bi <?php echo $iconArch; ?>"></i>
                    Ver archivo adjunto
                    <i class="bi bi-eye" style="font-size:.75rem;opacity:.7;"></i>
                </button>
                <?php endif; ?>

            </article>
            <?php endforeach; ?>

            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['codigo'])): ?>
            <!-- No debería llegar aquí, pero por si acaso -->

            <?php else: ?>
            <!-- Estado inicial: sin búsqueda -->
            <div class="estado-vacio">
                <div class="estado-vacio-icon"><i class="bi bi-search"></i></div>
                <h3>Consulta el estado de tu solicitud</h3>
                <p>Usa el formulario de arriba para buscar por código de radicado o por tu correo electrónico.</p>
            </div>
            <?php endif; ?>

        </section><!-- /resultados-section -->

    </main>

    <!-- ── FOOTER ─────────────────────────────────────────────────────────── -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <i class="bi bi-clipboard-data"></i>
                        <span>Sistema PQRS</span>
                    </div>
                    <p class="footer-text">
                        Plataforma oficial de gestión de Peticiones, Quejas, Reclamos, Sugerencias y Denuncias.
                    </p>
                </div>
                <div>
                    <h4 class="footer-title">Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="bi bi-pencil-square"></i> Nueva Solicitud</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/consulta"><i class="bi bi-search"></i> Consultar Estado</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/login"><i class="bi bi-shield-lock"></i> Panel Administrador</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Marco Legal</h4>
                    <ul class="footer-links">
                        <li><a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=62567" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i> Ley 1755 de 2015</a></li>
                        <li><a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=42761" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i> Ley 1437 de 2011</a></li>
                        <li><a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=44306" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i> Ley 1474 de 2011</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 Sistema PQRS — Todos los derechos reservados | Transparencia y cumplimiento legal</p>
            </div>
        </div>
    </footer>

    <script>
    // ── Tabs ──────────────────────────────────────────────────────────────────
    function cambiarTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('activo');
            b.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.busqueda-panel').forEach(p => p.classList.remove('activo'));

        document.getElementById('tab-' + tab).classList.add('activo');
        document.getElementById('tab-' + tab).setAttribute('aria-selected', 'true');
        document.getElementById('panel-' + tab).classList.add('activo');
    }

    // ── Auto-activar tab correcto si viene de POST ────────────────────────────
    (function () {
        const correoVal = '<?php echo addslashes($_POST['correo'] ?? ''); ?>';
        if (correoVal) cambiarTab('correo');
    })();

    // ── Animar barras de progreso al cargar ───────────────────────────────────
    window.addEventListener('load', function () {
        document.querySelectorAll('.progreso-fill').forEach(function (fill) {
            const target = fill.style.width;
            fill.style.width = '0';
            setTimeout(function () { fill.style.width = target; }, 100);
        });
    });
    </script>

    <!-- ── Modal Archivo Adjunto ───────────────────────────────────────── -->
    <div id="modalAdjunto" class="modal-adjunto-overlay" onclick="if(event.target===this)cerrarModalAdjunto()">
        <div class="modal-adjunto-box">
            <div class="modal-adjunto-header">
                <h3>
                    <i class="bi bi-paperclip"></i>
                    <span id="modalAdjuntoNombre">Archivo adjunto</span>
                </h3>
                <div class="modal-header-acciones">
                    <a id="modalAdjuntoDescargar" href="#" download class="modal-btn-descargar">
                        <i class="bi bi-download"></i> Descargar
                    </a>
                    <button class="modal-btn-cerrar" onclick="cerrarModalAdjunto()" title="Cerrar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="modal-adjunto-body" id="modalAdjuntoBody"></div>
        </div>
    </div>

    <script>
    function abrirModalAdjunto(url, nombre, ext) {
        const modal   = document.getElementById('modalAdjunto');
        const body    = document.getElementById('modalAdjuntoBody');
        const titulo  = document.getElementById('modalAdjuntoNombre');
        const descBtn = document.getElementById('modalAdjuntoDescargar');
        titulo.textContent = nombre;
        descBtn.href       = url;
        descBtn.download   = nombre;
        const imgs = ['jpg','jpeg','png','gif','webp'];
        if (imgs.includes(ext.toLowerCase())) {
            const img = document.createElement('img');
            img.alt = nombre;
            img.onerror = function() {
                body.innerHTML = `<div class="modal-adjunto-descarga"><i class="bi bi-exclamation-circle" style="color:#f59e0b"></i><p style="color:#fbbf24">No se pudo previsualizar la imagen.</p><p style="font-size:.8rem;color:#94a3b8">${nombre}</p><a href="${url}" download="${nombre}" class="modal-btn-descargar"><i class="bi bi-download"></i> Descargar</a></div>`;
            };
            img.src = url;
            body.innerHTML = '';
            body.appendChild(img);
        } else if (ext.toLowerCase() === 'pdf') {
            body.innerHTML = `<iframe src="${url}" title="${nombre}"></iframe>`;
        } else {
            body.innerHTML = `<div class="modal-adjunto-descarga"><i class="bi bi-file-earmark-arrow-down"></i><p>Este archivo no puede previsualizarse.</p><p style="font-size:.8rem;color:#64748b">${nombre}</p><a href="${url}" download="${nombre}" class="modal-btn-descargar"><i class="bi bi-download"></i> Descargar</a></div>`;
        }
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }
    function cerrarModalAdjunto() {
        document.getElementById('modalAdjunto').classList.remove('activo');
        document.getElementById('modalAdjuntoBody').innerHTML = '';
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModalAdjunto(); });
    </script>

</body>
</html>
