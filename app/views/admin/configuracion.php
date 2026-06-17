<?php
/**
 * HU-09 + HU-15 / RF22 + RF24: Configuración unificada
 * — Mi Perfil (nombre, correo, contraseña)
 * — Configuración del Sistema (días vencimiento, datos institucionales)
 */

include __DIR__ . '/../layouts/verificar_sesion.php';
include __DIR__ . '/../../../config/conexion.php';

$con = conexion();
$msg_perfil  = null; $tipo_perfil  = '';
$msg_sistema = null; $tipo_sistema = '';

// ── POST: Guardar perfil ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'perfil') {

    $nombre  = trim($_POST['nombre_completo']   ?? '');
    $correo  = trim($_POST['correo_electronico'] ?? '');
    $passAct = $_POST['password_actual']  ?? '';
    $passNew = $_POST['password_nueva']   ?? '';
    $passCon = $_POST['password_confirmar'] ?? '';

    if (empty($nombre) || empty($correo)) {
        $msg_perfil = 'Nombre y correo son obligatorios.';
        $tipo_perfil = 'error';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $msg_perfil = 'El correo electrónico no es válido.';
        $tipo_perfil = 'error';
    } else {
        $cambiarPass = false;
        $errorPass   = false;

        if (!empty($passNew)) {
            if (empty($passAct)) {
                $msg_perfil  = 'Ingrese su contraseña actual para cambiarla.';
                $tipo_perfil = 'error';
                $errorPass   = true;
            } elseif (strlen($passNew) < 6) {
                $msg_perfil  = 'La nueva contraseña debe tener al menos 6 caracteres.';
                $tipo_perfil = 'error';
                $errorPass   = true;
            } elseif ($passNew !== $passCon) {
                $msg_perfil  = 'Las contraseñas nuevas no coinciden.';
                $tipo_perfil = 'error';
                $errorPass   = true;
            } else {
                // Verificar contraseña actual
                $stmtV = mysqli_prepare($con, "SELECT contrasena FROM administrador WHERE id = ?");
                mysqli_stmt_bind_param($stmtV, 'i', $adminId);
                mysqli_stmt_execute($stmtV);
                $rowV = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtV));
                mysqli_stmt_close($stmtV);

                if (!$rowV || (!password_verify($passAct, $rowV['contrasena']) && $passAct !== $rowV['contrasena'])) {
                    $msg_perfil  = 'La contraseña actual es incorrecta.';
                    $tipo_perfil = 'error';
                    $errorPass   = true;
                } else {
                    $cambiarPass = true;
                }
            }
        }

        if (!$errorPass) {
            if ($cambiarPass) {
                $hash = password_hash($passNew, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($con, "UPDATE administrador SET nombre_completo=?, correo_electronico=?, contrasena=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $correo, $hash, $adminId);
            } else {
                $stmt = mysqli_prepare($con, "UPDATE administrador SET nombre_completo=?, correo_electronico=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'ssi', $nombre, $correo, $adminId);
            }

            if ($stmt && mysqli_stmt_execute($stmt)) {
                $_SESSION['admin_nombre'] = $nombre;
                $_SESSION['admin_correo'] = $correo;
                $adminNombre = $nombre;
                $adminCorreo = $correo;
                $msg_perfil  = 'Perfil actualizado correctamente.';
                $tipo_perfil = 'exito';
            } else {
                $msg_perfil  = 'Error al actualizar el perfil.';
                $tipo_perfil = 'error';
            }
            if ($stmt) mysqli_stmt_close($stmt);
        }
    }
}

// ── POST: Guardar configuración del sistema ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'sistema') {

    $campos_dias = ['dias_vencimiento_peticion','dias_vencimiento_queja',
                    'dias_vencimiento_reclamo','dias_vencimiento_sugerencia','dias_vencimiento_denuncia'];
    $valores = [];
    $err = false;

    foreach ($campos_dias as $c) {
        $v = intval($_POST[$c] ?? 0);
        if ($v < 1 || $v > 30) {
            $msg_sistema  = "El valor para '$c' debe estar entre 1 y 30.";
            $tipo_sistema = 'error';
            $err = true;
            break;
        }
        $valores[$c] = $v;
    }

    if (!$err) {
        $correo_noti  = trim($_POST['correo_notificaciones'] ?? '');
        $nombre_emp   = trim($_POST['nombre_empresa']        ?? '');

        if (!empty($correo_noti) && !filter_var($correo_noti, FILTER_VALIDATE_EMAIL)) {
            $msg_sistema  = 'El correo de notificaciones no es válido.';
            $tipo_sistema = 'error';
            $err = true;
        }
    }

    if (!$err) {
        $stmt = $con->prepare(
            "UPDATE configuracion_sistema SET
                dias_vencimiento_peticion=?, dias_vencimiento_queja=?,
                dias_vencimiento_reclamo=?, dias_vencimiento_sugerencia=?,
                dias_vencimiento_denuncia=?, correo_notificaciones=?, nombre_empresa=?
             WHERE id=1"
        );
        if ($stmt) {
            $stmt->bind_param('iiiiiss',
                $valores['dias_vencimiento_peticion'], $valores['dias_vencimiento_queja'],
                $valores['dias_vencimiento_reclamo'],  $valores['dias_vencimiento_sugerencia'],
                $valores['dias_vencimiento_denuncia'], $correo_noti, $nombre_emp);
            if ($stmt->execute()) {
                $msg_sistema  = 'Configuración del sistema guardada correctamente.';
                $tipo_sistema = 'exito';
            } else {
                $msg_sistema  = 'Error al guardar la configuración.';
                $tipo_sistema = 'error';
            }
            $stmt->close();
        }
    }
}

// ── Leer configuración actual del sistema ─────────────────────────────────────
$config = [];
$res = mysqli_query($con, "SELECT * FROM configuracion_sistema WHERE id=1");
if ($res) $config = mysqli_fetch_assoc($res) ?? [];
$config = array_merge([
    'dias_vencimiento_peticion'   => 15,
    'dias_vencimiento_queja'      => 15,
    'dias_vencimiento_reclamo'    => 15,
    'dias_vencimiento_sugerencia' => 15,
    'dias_vencimiento_denuncia'   => 15,
    'correo_notificaciones'       => '',
    'nombre_empresa'              => '',
], $config);

mysqli_close($con);

// Tab activo: si el POST fue de sistema, abrir esa tab; si no, perfil por defecto
$tabActiva = (isset($_POST['accion']) && $_POST['accion'] === 'sistema') ? 'sistema' : 'perfil';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
    $baseUrl = $isRailway ? '/' : '/PROYECTO_PQRS/';
    ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>css/estilos.css">
    <style>
        /* ── Tabs ─────────────────────────────────────────────────────────── */
        .cfg-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--color-gray-200);
            margin-bottom: var(--space-6);
        }
        .cfg-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3) var(--space-6);
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--color-gray-500);
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .cfg-tab-btn:hover { color: var(--color-primary); }
        .cfg-tab-btn.activo {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
        }
        .cfg-tab-btn i { font-size: 1.1rem; }

        .cfg-panel { display: none; }
        .cfg-panel.activo { display: block; }

        /* ── Grid 2 columnas ──────────────────────────────────────────────── */
        .cfg-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-6);
        }
        @media (max-width: 768px) { .cfg-grid { grid-template-columns: 1fr; } }

        /* ── Tarjetas ─────────────────────────────────────────────────────── */
        .cfg-card {
            background: var(--color-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-gray-100);
            overflow: hidden;
        }
        .cfg-card-header {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--color-gray-100);
            background: var(--color-gray-50);
        }
        .cfg-card-header h2 {
            font-size: var(--font-size-base);
            font-weight: 700;
            color: var(--color-gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        .cfg-card-header h2 i { color: var(--color-primary); }
        .cfg-card-body { padding: var(--space-5); }

        /* ── Campos ───────────────────────────────────────────────────────── */
        .cfg-grupo { margin-bottom: var(--space-4); }
        .cfg-grupo:last-child { margin-bottom: 0; }
        .cfg-label {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--color-gray-700);
            margin-bottom: var(--space-2);
        }
        .cfg-label i { color: var(--color-primary); }
        .cfg-input {
            width: 100%;
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-family: inherit;
            color: var(--color-gray-800);
            transition: border-color var(--transition-fast);
        }
        .cfg-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(30,64,175,.1);
        }
        .cfg-input-num {
            width: 90px;
            text-align: center;
        }
        .cfg-input-wrap { display: flex; align-items: center; gap: var(--space-2); }
        .cfg-unidad { font-size: var(--font-size-sm); color: var(--color-gray-500); }
        .cfg-ayuda { font-size: var(--font-size-xs); color: var(--color-gray-400); margin-top: var(--space-1); }

        .cfg-badge {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: var(--radius-full);
            font-size: var(--font-size-xs); font-weight: 600;
        }

        /* ── Alertas ──────────────────────────────────────────────────────── */
        .cfg-alerta {
            display: flex; align-items: center; gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-5);
            font-size: var(--font-size-sm); font-weight: 500;
        }
        .cfg-alerta i { font-size: 1.1rem; flex-shrink: 0; }
        .cfg-alerta.exito { background:#f0fdf4; border:1px solid #bbf7d0; border-left:4px solid #059669; color:#065f46; }
        .cfg-alerta.error { background:#fef2f2; border:1px solid #fecaca; border-left:4px solid #dc2626; color:#991b1b; }

        /* ── Acciones ─────────────────────────────────────────────────────── */
        .cfg-acciones {
            display: flex; gap: var(--space-3); justify-content: flex-end;
            margin-top: var(--space-6);
            padding-top: var(--space-5);
            border-top: 1px solid var(--color-gray-100);
        }
        .cfg-btn-cancel {
            display: inline-flex; align-items: center; gap: var(--space-2);
            padding: var(--space-2) var(--space-5);
            background: var(--color-gray-100); color: var(--color-gray-700);
            border: 1px solid var(--color-gray-300); border-radius: var(--radius-md);
            font-weight: 600; font-size: var(--font-size-sm);
            text-decoration: none; transition: all var(--transition-fast);
        }
        .cfg-btn-cancel:hover { background: var(--color-gray-200); }
        .cfg-btn-save {
            display: inline-flex; align-items: center; gap: var(--space-2);
            padding: var(--space-2) var(--space-5);
            background: var(--color-primary); color: white;
            border: none; border-radius: var(--radius-md);
            font-weight: 600; font-size: var(--font-size-sm);
            cursor: pointer; transition: all var(--transition-fast);
        }
        .cfg-btn-save:hover { background: var(--color-primary-dark); }

        .info-legal {
            background:#eff6ff; border:1px solid #bfdbfe; border-radius:var(--radius-md);
            padding:var(--space-3) var(--space-4); font-size:var(--font-size-xs); color:#1e40af;
            margin-top:var(--space-4); display:flex; gap:var(--space-2); align-items:flex-start;
        }
        .info-legal i { flex-shrink:0; margin-top:1px; }

        /* Preview días */
        .preview-dias {
            margin-top: var(--space-4);
            padding: var(--space-4);
            background: var(--color-gray-50);
            border-radius: var(--radius-md);
            border: 1px solid var(--color-gray-200);
        }
        .preview-titulo {
            font-size: var(--font-size-xs); font-weight: 700;
            color: var(--color-gray-500); text-transform: uppercase;
            letter-spacing: .05em; margin-bottom: var(--space-3);
        }
        .preview-row {
            display: flex; justify-content: space-between;
            padding: 4px 0; font-size: var(--font-size-xs);
            border-bottom: 1px solid var(--color-gray-100);
        }
        .preview-row:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <section class="dashboard-section">
        <div class="container">

            <!-- Volver -->
            <div style="margin-bottom:var(--space-6);">
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/dashboard"
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
                        Configuración
                    </h1>
                    <p class="dashboard-subtitle">Gestione su perfil y los parámetros del sistema</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="cfg-tabs" role="tablist">
                <button class="cfg-tab-btn <?php echo $tabActiva === 'perfil' ? 'activo' : ''; ?>"
                        id="tab-perfil" role="tab" aria-controls="panel-perfil"
                        onclick="cambiarTab('perfil')">
                    <i class="bi bi-person-circle"></i>
                    Mi Perfil
                </button>
                <button class="cfg-tab-btn <?php echo $tabActiva === 'sistema' ? 'activo' : ''; ?>"
                        id="tab-sistema" role="tab" aria-controls="panel-sistema"
                        onclick="cambiarTab('sistema')">
                    <i class="bi bi-gear"></i>
                    Sistema
                </button>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 PANEL: MI PERFIL
            ══════════════════════════════════════════════════════════════ -->
            <div id="panel-perfil" class="cfg-panel <?php echo $tabActiva === 'perfil' ? 'activo' : ''; ?>"
                 role="tabpanel">

                <?php if ($msg_perfil): ?>
                <div class="cfg-alerta <?php echo $tipo_perfil; ?>">
                    <i class="bi bi-<?php echo $tipo_perfil === 'exito' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
                    <?php echo htmlspecialchars($msg_perfil); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="configuracion.php">
                    <input type="hidden" name="accion" value="perfil">

                    <div class="cfg-grid">

                        <!-- Datos personales -->
                        <div class="cfg-card">
                            <div class="cfg-card-header">
                                <h2><i class="bi bi-person"></i> Datos Personales</h2>
                            </div>
                            <div class="cfg-card-body">
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="nombre_completo">
                                        <i class="bi bi-person"></i> Nombre Completo <span style="color:#dc2626">*</span>
                                    </label>
                                    <input type="text" id="nombre_completo" name="nombre_completo"
                                           class="cfg-input"
                                           value="<?php echo htmlspecialchars($adminNombre); ?>"
                                           required maxlength="150">
                                </div>
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="correo_electronico">
                                        <i class="bi bi-envelope"></i> Correo Electrónico <span style="color:#dc2626">*</span>
                                    </label>
                                    <input type="email" id="correo_electronico" name="correo_electronico"
                                           class="cfg-input"
                                           value="<?php echo htmlspecialchars($adminCorreo); ?>"
                                           required maxlength="150">
                                </div>
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="p_usuario">
                                        <i class="bi bi-at"></i> Usuario
                                    </label>
                                    <input type="text" id="p_usuario" class="cfg-input"
                                           value="<?php echo htmlspecialchars($adminUsuario ?? ''); ?>"
                                           disabled
                                           style="background:var(--color-gray-50);color:var(--color-gray-400);">
                                    <p class="cfg-ayuda">El nombre de usuario no se puede modificar.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cambio de contraseña -->
                        <div class="cfg-card">
                            <div class="cfg-card-header">
                                <h2><i class="bi bi-key"></i> Cambiar Contraseña</h2>
                            </div>
                            <div class="cfg-card-body">
                                <div style="padding:var(--space-3) var(--space-4);background:#fef3c7;border-radius:var(--radius-md);border:1px solid #fcd34d;font-size:var(--font-size-xs);color:#92400e;margin-bottom:var(--space-4);">
                                    <i class="bi bi-info-circle"></i>
                                    Deje los campos en blanco si <strong>no</strong> desea cambiar la contraseña.
                                </div>
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="password_actual">
                                        <i class="bi bi-lock"></i> Contraseña Actual
                                    </label>
                                    <input type="password" id="password_actual" name="password_actual"
                                           class="cfg-input" placeholder="Ingrese su contraseña actual"
                                           autocomplete="current-password">
                                </div>
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="password_nueva">
                                        <i class="bi bi-lock-fill"></i> Nueva Contraseña
                                    </label>
                                    <input type="password" id="password_nueva" name="password_nueva"
                                           class="cfg-input" placeholder="Mínimo 6 caracteres"
                                           autocomplete="new-password">
                                </div>
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="password_confirmar">
                                        <i class="bi bi-lock-fill"></i> Confirmar Nueva Contraseña
                                    </label>
                                    <input type="password" id="password_confirmar" name="password_confirmar"
                                           class="cfg-input" placeholder="Repita la nueva contraseña"
                                           autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                    </div><!-- /cfg-grid -->

                    <div class="cfg-acciones">
                        <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/dashboard" class="cfg-btn-cancel">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="cfg-btn-save">
                            <i class="bi bi-check-lg"></i> Guardar Perfil
                        </button>
                    </div>
                </form>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 PANEL: SISTEMA
            ══════════════════════════════════════════════════════════════ -->
            <div id="panel-sistema" class="cfg-panel <?php echo $tabActiva === 'sistema' ? 'activo' : ''; ?>"
                 role="tabpanel">

                <?php if ($msg_sistema): ?>
                <div class="cfg-alerta <?php echo $tipo_sistema; ?>">
                    <i class="bi bi-<?php echo $tipo_sistema === 'exito' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
                    <?php echo htmlspecialchars($msg_sistema); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="configuracion.php" id="formSistema">
                    <input type="hidden" name="accion" value="sistema">

                    <?php
                    $tipos = [
                        'peticion'   => ['label'=>'Petición',  'icon'=>'bi-file-text',            'color'=>'#dbeafe','text'=>'#1e40af'],
                        'queja'      => ['label'=>'Queja',     'icon'=>'bi-exclamation-circle',   'color'=>'#fee2e2','text'=>'#991b1b'],
                        'reclamo'    => ['label'=>'Reclamo',   'icon'=>'bi-exclamation-triangle', 'color'=>'#fef3c7','text'=>'#92400e'],
                        'sugerencia' => ['label'=>'Sugerencia','icon'=>'bi-lightbulb',            'color'=>'#d1fae5','text'=>'#065f46'],
                        'denuncia'   => ['label'=>'Denuncia',  'icon'=>'bi-megaphone',            'color'=>'#ede9fe','text'=>'#5b21b6'],
                    ];
                    ?>

                    <div class="cfg-grid">

                        <!-- Días de vencimiento -->
                        <div class="cfg-card">
                            <div class="cfg-card-header">
                                <h2><i class="bi bi-clock"></i> Días de Vencimiento por Tipo</h2>
                            </div>
                            <div class="cfg-card-body">
                                <?php foreach ($tipos as $key => $tipo):
                                    $campo = 'dias_vencimiento_' . $key;
                                    $val   = (int)($config[$campo] ?? 15);
                                ?>
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="<?php echo $campo; ?>">
                                        <i class="bi <?php echo $tipo['icon']; ?>"></i>
                                        <span class="cfg-badge"
                                              style="background:<?php echo $tipo['color']; ?>;color:<?php echo $tipo['text']; ?>;">
                                            <?php echo $tipo['label']; ?>
                                        </span>
                                    </label>
                                    <div class="cfg-input-wrap">
                                        <input type="number" id="<?php echo $campo; ?>"
                                               name="<?php echo $campo; ?>"
                                               class="cfg-input cfg-input-num"
                                               value="<?php echo $val; ?>"
                                               min="1" max="30" required>
                                        <span class="cfg-unidad">días hábiles</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div class="info-legal">
                                    <i class="bi bi-info-circle"></i>
                                    <span>Valores entre <strong>1 y 30</strong> días. Ley 1755 de 2015 establece <strong>15 días hábiles</strong> como estándar.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Datos institucionales + Preview -->
                        <div class="cfg-card">
                            <div class="cfg-card-header">
                                <h2><i class="bi bi-building"></i> Datos Institucionales</h2>
                            </div>
                            <div class="cfg-card-body">
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="nombre_empresa">
                                        <i class="bi bi-building"></i> Nombre de la Empresa / Entidad
                                    </label>
                                    <input type="text" id="nombre_empresa" name="nombre_empresa"
                                           class="cfg-input"
                                           value="<?php echo htmlspecialchars($config['nombre_empresa'] ?? ''); ?>"
                                           placeholder="Ej: Empresa de Servicios Públicos de Neiva"
                                           maxlength="150">
                                    <p class="cfg-ayuda">Se muestra en reportes y correos de confirmación.</p>
                                </div>
                                <div class="cfg-grupo">
                                    <label class="cfg-label" for="correo_notificaciones">
                                        <i class="bi bi-envelope"></i> Correo de Notificaciones
                                    </label>
                                    <input type="email" id="correo_notificaciones" name="correo_notificaciones"
                                           class="cfg-input"
                                           value="<?php echo htmlspecialchars($config['correo_notificaciones'] ?? ''); ?>"
                                           placeholder="Ej: pqrs@empresa.com"
                                           maxlength="150">
                                    <p class="cfg-ayuda">Recibe copias internas de las notificaciones del sistema.</p>
                                </div>

                                <!-- Vista previa -->
                                <div class="preview-dias">
                                    <p class="preview-titulo"><i class="bi bi-eye"></i> Vista previa de términos</p>
                                    <?php foreach ($tipos as $key => $tipo):
                                        $campo = 'dias_vencimiento_' . $key;
                                        $val   = (int)($config[$campo] ?? 15);
                                    ?>
                                    <div class="preview-row">
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

                    </div><!-- /cfg-grid -->

                    <div class="cfg-acciones">
                        <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/dashboard" class="cfg-btn-cancel">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="cfg-btn-save">
                            <i class="bi bi-check-lg"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </section>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
    // ── Tabs ──────────────────────────────────────────────────────────────────
    function cambiarTab(tab) {
        document.querySelectorAll('.cfg-tab-btn').forEach(function(b) {
            b.classList.remove('activo');
        });
        document.querySelectorAll('.cfg-panel').forEach(function(p) {
            p.classList.remove('activo');
        });
        document.getElementById('tab-' + tab).classList.add('activo');
        document.getElementById('panel-' + tab).classList.add('activo');
    }

    // ── Vista previa en tiempo real ───────────────────────────────────────────
    (function () {
        var tipos = ['peticion','queja','reclamo','sugerencia','denuncia'];
        tipos.forEach(function(tipo) {
            var input   = document.getElementById('dias_vencimiento_' + tipo);
            var preview = document.getElementById('preview-' + tipo);
            if (!input || !preview) return;
            input.addEventListener('input', function() {
                var v = parseInt(this.value, 10);
                if (!isNaN(v) && v >= 1 && v <= 30) {
                    preview.textContent = v + ' días';
                    input.style.borderColor = '';
                } else {
                    preview.textContent = '—';
                    input.style.borderColor = '#dc2626';
                }
            });
        });

        // Validación formulario sistema
        var formSistema = document.getElementById('formSistema');
        if (formSistema) {
            formSistema.addEventListener('submit', function(e) {
                var ok = true;
                tipos.forEach(function(tipo) {
                    var input = document.getElementById('dias_vencimiento_' + tipo);
                    var v = parseInt(input.value, 10);
                    if (isNaN(v) || v < 1 || v > 30) {
                        input.style.borderColor = '#dc2626';
                        ok = false;
                    } else {
                        input.style.borderColor = '';
                    }
                });
                var cInp = document.getElementById('correo_notificaciones');
                var cv = cInp.value.trim();
                if (cv && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cv)) {
                    cInp.style.borderColor = '#dc2626';
                    ok = false;
                } else {
                    cInp.style.borderColor = '';
                }
                if (!ok) {
                    e.preventDefault();
                    alert('Corrija los campos marcados antes de guardar.');
                }
            });
        }
    })();
    </script>
</body>
</html>
