<?php
/**
 * HU-15 / RF24: Configuración del Sistema
 * Permite al administrador ajustar parámetros operativos desde el panel
 */

include '../includes/verificar_sesion.php';
include '../config/conexion.php';

$con = conexion();
$mensaje     = null;
$tipo_mensaje = '';

// ── Procesar POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recoger y validar días (entre 1 y 30)
    $campos_dias = [
        'dias_vencimiento_peticion',
        'dias_vencimiento_queja',
        'dias_vencimiento_reclamo',
        'dias_vencimiento_sugerencia',
        'dias_vencimiento_denuncia',
    ];

    $valores = [];
    $error_validacion = false;

    foreach ($campos_dias as $campo) {
        $val = intval($_POST[$campo] ?? 0);
        if ($val < 1 || $val > 30) {
            $mensaje = "El valor de '$campo' debe estar entre 1 y 30 días.";
            $tipo_mensaje = 'error';
            $error_validacion = true;
            break;
        }
        $valores[$campo] = $val;
    }

    if (!$error_validacion) {
        $correo_notificaciones = trim($_POST['correo_notificaciones'] ?? '');
        $nombre_empresa        = trim($_POST['nombre_empresa']        ?? '');

        if (!empty($correo_notificaciones) && !filter_var($correo_notificaciones, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'El correo de notificaciones no es válido.';
            $tipo_mensaje = 'error';
            $error_validacion = true;
        }
    }

    if (!$error_validacion) {
        $stmt = $con->prepare(
            "UPDATE configuracion_sistema SET
                dias_vencimiento_peticion   = ?,
                dias_vencimiento_queja      = ?,
                dias_vencimiento_reclamo    = ?,
                dias_vencimiento_sugerencia = ?,
                dias_vencimiento_denuncia   = ?,
                correo_notificaciones       = ?,
                nombre_empresa              = ?
             WHERE id = 1"
        );

        if ($stmt) {
            $stmt->bind_param(
                'iiiiiss',
                $valores['dias_vencimiento_peticion'],
                $valores['dias_vencimiento_queja'],
                $valores['dias_vencimiento_reclamo'],
                $valores['dias_vencimiento_sugerencia'],
                $valores['dias_vencimiento_denuncia'],
                $correo_notificaciones,
                $nombre_empresa
            );

            if ($stmt->execute()) {
                $mensaje = 'Configuración guardada correctamente.';
                $tipo_mensaje = 'exito';
            } else {
                $mensaje = 'Error al guardar la configuración. Intente nuevamente.';
                $tipo_mensaje = 'error';
            }
            $stmt->close();
        } else {
            $mensaje = 'Error interno al preparar la consulta.';
            $tipo_mensaje = 'error';
        }
    }
}

// ── Leer configuración actual ──────────────────────────────────────────────────
$config = [];
$result = mysqli_query($con, "SELECT * FROM configuracion_sistema WHERE id = 1");
if ($result) {
    $config = mysqli_fetch_assoc($result) ?? [];
}
mysqli_close($con);

// Valores por defecto si la tabla está vacía
$config = array_merge([
    'dias_vencimiento_peticion'   => 15,
    'dias_vencimiento_queja'      => 15,
    'dias_vencimiento_reclamo'    => 15,
    'dias_vencimiento_sugerencia' => 15,
    'dias_vencimiento_denuncia'   => 15,
    'correo_notificaciones'       => '',
    'nombre_empresa'              => '',
], $config);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema - PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
    $baseUrl = $isRailway ? '/' : '/PROYECTO_PQRS/';
    ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>css/estilos.css">
    <style>
        /* ── Estilos específicos de configuración ─────────────────────────── */
        .config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-6);
        }
        @media (max-width: 768px) {
            .config-grid { grid-template-columns: 1fr; }
        }

        .config-card {
            background: var(--color-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-gray-100);
            overflow: hidden;
        }
        .config-card-full {
            grid-column: 1 / -1;
        }
        .config-card-header {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--color-gray-100);
            background: var(--color-gray-50);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }
        .config-card-header h2 {
            font-size: var(--font-size-base);
            font-weight: 700;
            color: var(--color-gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        .config-card-header h2 i { color: var(--color-primary); }
        .config-card-body { padding: var(--space-5); }

        .config-campo {
            margin-bottom: var(--space-4);
        }
        .config-campo:last-child { margin-bottom: 0; }

        .config-label {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--color-gray-700);
            margin-bottom: var(--space-2);
        }
        .config-label i { color: var(--color-primary); font-size: 1rem; }

        .config-input-wrap {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        .config-input {
            width: 90px;
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-family: inherit;
            color: var(--color-gray-800);
            transition: border-color var(--transition-fast);
            text-align: center;
        }
        .config-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        .config-input-full {
            width: 100%;
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-family: inherit;
            color: var(--color-gray-800);
            transition: border-color var(--transition-fast);
        }
        .config-input-full:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        .config-unidad {
            font-size: var(--font-size-sm);
            color: var(--color-gray-500);
        }
        .config-ayuda {
            font-size: var(--font-size-xs);
            color: var(--color-gray-400);
            margin-top: var(--space-1);
        }

        .config-tipo-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-1);
            padding: 2px 8px;
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            margin-bottom: var(--space-1);
        }

        .config-acciones {
            display: flex;
            gap: var(--space-3);
            justify-content: flex-end;
            padding: var(--space-5);
            border-top: 1px solid var(--color-gray-100);
            background: var(--color-gray-50);
        }

        .alerta-config {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-4) var(--space-5);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-5);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        .alerta-config i { font-size: 1.25rem; flex-shrink: 0; }
        .alerta-config.exito {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #059669;
            color: #065f46;
        }
        .alerta-config.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid var(--color-danger);
            color: #991b1b;
        }

        .info-legal {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: var(--radius-md);
            padding: var(--space-3) var(--space-4);
            font-size: var(--font-size-xs);
            color: #1e40af;
            margin-top: var(--space-3);
            display: flex;
            gap: var(--space-2);
            align-items: flex-start;
        }
        .info-legal i { flex-shrink: 0; margin-top: 1px; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="dashboard-section">
        <div class="container">

            <!-- Navegación -->
            <div class="detalle-nav" style="margin-bottom:var(--space-6);">
                <a href="dashboard_admin.php" class="btn-volver-detalle"
                   style="display:inline-flex;align-items:center;gap:var(--space-2);color:var(--color-gray-500);font-size:var(--font-size-sm);font-weight:500;padding:var(--space-2) var(--space-4);border:1px solid var(--color-gray-200);border-radius:var(--radius-lg);background:var(--color-white);transition:all var(--transition-fast);text-decoration:none;">
                    <i class="bi bi-arrow-left"></i>
                    Volver al Dashboard
                </a>
            </div>

            <!-- Encabezado -->
            <div class="dashboard-welcome">
                <div>
                    <h1 class="dashboard-title">
                        <i class="bi bi-sliders"></i>
                        Configuración del Sistema
                    </h1>
                    <p class="dashboard-subtitle">
                        Ajuste los parámetros operativos del sistema PQRS
                    </p>
                </div>
            </div>

            <!-- Mensaje de éxito / error -->
            <?php if ($mensaje): ?>
            <div class="alerta-config <?php echo $tipo_mensaje; ?>">
                <i class="bi bi-<?php echo $tipo_mensaje === 'exito' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="configuracion.php" id="formConfig">

                <div class="config-grid">

                    <!-- ── Días de vencimiento ──────────────────────────────── -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h2><i class="bi bi-clock"></i> Días de Vencimiento por Tipo</h2>
                        </div>
                        <div class="config-card-body">

                            <?php
                            $tipos = [
                                'peticion'   => ['label' => 'Petición',   'icon' => 'bi-file-text',           'color' => '#dbeafe', 'text' => '#1e40af'],
                                'queja'      => ['label' => 'Queja',      'icon' => 'bi-exclamation-circle',  'color' => '#fee2e2', 'text' => '#991b1b'],
                                'reclamo'    => ['label' => 'Reclamo',    'icon' => 'bi-exclamation-triangle','color' => '#fef3c7', 'text' => '#92400e'],
                                'sugerencia' => ['label' => 'Sugerencia', 'icon' => 'bi-lightbulb',           'color' => '#d1fae5', 'text' => '#065f46'],
                                'denuncia'   => ['label' => 'Denuncia',   'icon' => 'bi-megaphone',           'color' => '#ede9fe', 'text' => '#5b21b6'],
                            ];
                            foreach ($tipos as $key => $tipo):
                                $campo  = 'dias_vencimiento_' . $key;
                                $valor  = (int)($config[$campo] ?? 15);
                            ?>
                            <div class="config-campo">
                                <label class="config-label" for="<?php echo $campo; ?>">
                                    <i class="bi <?php echo $tipo['icon']; ?>"></i>
                                    <span class="config-tipo-badge"
                                          style="background:<?php echo $tipo['color']; ?>;color:<?php echo $tipo['text']; ?>;">
                                        <?php echo $tipo['label']; ?>
                                    </span>
                                </label>
                                <div class="config-input-wrap">
                                    <input type="number"
                                           id="<?php echo $campo; ?>"
                                           name="<?php echo $campo; ?>"
                                           class="config-input"
                                           value="<?php echo $valor; ?>"
                                           min="1" max="30" required>
                                    <span class="config-unidad">días hábiles</span>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <div class="info-legal">
                                <i class="bi bi-info-circle"></i>
                                <span>Los valores deben estar entre <strong>1 y 30</strong> días. Según la Ley 1755 de 2015, el término estándar es <strong>15 días hábiles</strong>.</span>
                            </div>
                        </div>
                    </div>

                    <!-- ── Datos institucionales ────────────────────────────── -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h2><i class="bi bi-building"></i> Datos Institucionales</h2>
                        </div>
                        <div class="config-card-body">

                            <div class="config-campo">
                                <label class="config-label" for="nombre_empresa">
                                    <i class="bi bi-building"></i>
                                    Nombre de la Empresa / Entidad
                                </label>
                                <input type="text"
                                       id="nombre_empresa"
                                       name="nombre_empresa"
                                       class="config-input-full"
                                       value="<?php echo htmlspecialchars($config['nombre_empresa'] ?? ''); ?>"
                                       placeholder="Ej: Empresa de Servicios Públicos de Neiva"
                                       maxlength="150">
                                <p class="config-ayuda">Aparece en reportes y correos de confirmación.</p>
                            </div>

                            <div class="config-campo">
                                <label class="config-label" for="correo_notificaciones">
                                    <i class="bi bi-envelope"></i>
                                    Correo de Notificaciones
                                </label>
                                <input type="email"
                                       id="correo_notificaciones"
                                       name="correo_notificaciones"
                                       class="config-input-full"
                                       value="<?php echo htmlspecialchars($config['correo_notificaciones'] ?? ''); ?>"
                                       placeholder="Ej: pqrs@empresa.com"
                                       maxlength="150">
                                <p class="config-ayuda">Correo donde el sistema envía copias de las notificaciones internas.</p>
                            </div>

                            <!-- Resumen de configuración actual -->
                            <div style="margin-top:var(--space-5);padding:var(--space-4);background:var(--color-gray-50);border-radius:var(--radius-md);border:1px solid var(--color-gray-200);">
                                <p style="font-size:var(--font-size-xs);font-weight:700;color:var(--color-gray-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:var(--space-3);">
                                    <i class="bi bi-eye"></i> Vista previa de términos actuales
                                </p>
                                <?php foreach ($tipos as $key => $tipo):
                                    $campo = 'dias_vencimiento_' . $key;
                                    $val   = (int)($config[$campo] ?? 15);
                                ?>
                                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:var(--font-size-xs);border-bottom:1px solid var(--color-gray-100);">
                                    <span style="color:var(--color-gray-600);">
                                        <i class="bi <?php echo $tipo['icon']; ?>" style="margin-right:4px;"></i>
                                        <?php echo $tipo['label']; ?>
                                    </span>
                                    <strong id="preview-<?php echo $key; ?>" style="color:var(--color-primary);">
                                        <?php echo $val; ?> días
                                    </strong>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div><!-- /config-grid -->

                <!-- Acciones -->
                <div class="config-acciones" style="margin-top:var(--space-5);">
                    <a href="dashboard_admin.php" class="btn btn-secondary"
                       style="display:inline-flex;align-items:center;gap:var(--space-2);padding:var(--space-2) var(--space-5);background:var(--color-gray-100);color:var(--color-gray-700);border:1px solid var(--color-gray-300);border-radius:var(--radius-md);font-weight:600;font-size:var(--font-size-sm);text-decoration:none;transition:all var(--transition-fast);">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary"
                            style="display:inline-flex;align-items:center;gap:var(--space-2);padding:var(--space-2) var(--space-5);background:var(--color-primary);color:white;border:none;border-radius:var(--radius-md);font-weight:600;font-size:var(--font-size-sm);cursor:pointer;transition:all var(--transition-fast);">
                        <i class="bi bi-check-lg"></i>
                        Guardar Configuración
                    </button>
                </div>

            </form>

        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script>
    // Actualizar la vista previa en tiempo real al cambiar valores
    (function () {
        const tipos = ['peticion', 'queja', 'reclamo', 'sugerencia', 'denuncia'];
        tipos.forEach(function (tipo) {
            var input   = document.getElementById('dias_vencimiento_' + tipo);
            var preview = document.getElementById('preview-' + tipo);
            if (!input || !preview) return;

            input.addEventListener('input', function () {
                var val = parseInt(this.value, 10);
                if (!isNaN(val) && val >= 1 && val <= 30) {
                    preview.textContent = val + ' días';
                    input.style.borderColor = '';
                } else {
                    preview.textContent = '—';
                    input.style.borderColor = '#dc2626';
                }
            });
        });

        // Validación del formulario
        document.getElementById('formConfig').addEventListener('submit', function (e) {
            var valido = true;
            tipos.forEach(function (tipo) {
                var input = document.getElementById('dias_vencimiento_' + tipo);
                var val   = parseInt(input.value, 10);
                if (isNaN(val) || val < 1 || val > 30) {
                    input.style.borderColor = '#dc2626';
                    valido = false;
                } else {
                    input.style.borderColor = '';
                }
            });

            var correoInput = document.getElementById('correo_notificaciones');
            var correo = correoInput.value.trim();
            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                correoInput.style.borderColor = '#dc2626';
                valido = false;
            } else {
                correoInput.style.borderColor = '';
            }

            if (!valido) {
                e.preventDefault();
                alert('Corrija los campos marcados en rojo antes de guardar.');
            }
        });
    })();
    </script>
</body>
</html>
