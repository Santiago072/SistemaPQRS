<?php
/**
 * HU-06: Consulta de Estado de PQRS
 * Permite consultar por código de radicado o correo electrónico
 */

require_once '../config/conexion.php';

$resultados    = [];
$error         = null;
$busqueda      = null;
$tipoBusqueda  = null;

// ── Estados con etiqueta, color e ícono ──────────────────────────────────────
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

// ── Procesar búsqueda ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['codigo'])) {

    $con = conexion();
    if (!$con) {
        $error = 'Error de conexión con la base de datos.';
    } else {

        // Determinar origen del parámetro
        $codigo = trim($_POST['codigo'] ?? $_GET['codigo'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if (!empty($codigo)) {
            // ── Búsqueda por código ──────────────────────────────────────────
            $codigo_safe = mysqli_real_escape_string($con, strtoupper($codigo));
            $sql = "SELECT p.*, u.tipo_persona, u.nombre_completo, u.correo_electronico,
                           u.correo_corporativo, u.nombre_representante
                    FROM pqrs p
                    LEFT JOIN usuario u ON p.usuario_id = u.id
                    WHERE p.codigo_radicado = '$codigo_safe'
                    LIMIT 1";

            $result = mysqli_query($con, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $resultados[]  = mysqli_fetch_assoc($result);
                $tipoBusqueda  = 'codigo';
                $busqueda      = $codigo_safe;
            } else {
                $error = "No se encontró ninguna solicitud con el código <strong>$codigo_safe</strong>. Verifique que el código sea correcto.";
            }

        } elseif (!empty($correo)) {
            // ── Búsqueda por correo ──────────────────────────────────────────
            $correo_safe = mysqli_real_escape_string($con, strtolower($correo));
            $sql = "SELECT p.*, u.tipo_persona, u.nombre_completo, u.correo_electronico,
                           u.correo_corporativo, u.nombre_representante
                    FROM pqrs p
                    LEFT JOIN usuario u ON p.usuario_id = u.id
                    WHERE LOWER(u.correo_electronico) = '$correo_safe'
                       OR LOWER(u.correo_corporativo) = '$correo_safe'
                    ORDER BY p.fecha_radicacion DESC";

            $result = mysqli_query($con, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $resultados[] = $row;
                }
                $tipoBusqueda = 'correo';
                $busqueda     = $correo_safe;
            } else {
                $error = "No se encontraron solicitudes asociadas al correo <strong>$correo_safe</strong>.";
            }

        } else {
            $error = 'Ingrese un código de radicado o un correo electrónico para buscar.';
        }

        mysqli_close($con);
    }
}

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
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="consulta-page">

    <!-- ── HEADER ─────────────────────────────────────────────────────────── -->
    <header class="header">
        <div class="container header-container">
            <a href="../index.php" class="logo" aria-label="Inicio - Sistema PQRS">
                <span class="logo-icon"><i class="bi bi-clipboard-data"></i></span>
                <span>Sistema PQRS</span>
            </a>
            <nav class="nav-admin">
                <a href="login.php" class="btn btn-outline">
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
                    <form method="POST" action="consulta_pqrs.php">
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
                    <form method="POST" action="consulta_pqrs.php">
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
                <?php if (!empty($pqrs['archivo_adjunto'])): ?>
                <a href="<?php echo htmlspecialchars($pqrs['archivo_adjunto']); ?>"
                   class="adjunto-link" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-paperclip"></i>
                    Ver archivo adjunto
                </a>
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
                        <li><a href="terminos.php"><i class="bi bi-pencil-square"></i> Nueva Solicitud</a></li>
                        <li><a href="consultar.php"><i class="bi bi-search"></i> Consultar Estado</a></li>
                        <li><a href="login.php"><i class="bi bi-shield-lock"></i> Panel Administrador</a></li>
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

</body>
</html>