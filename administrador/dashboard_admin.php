<?php
/* HU-Dashboard: Panel de Control del Administrador 
 * Página protegida que requiere sesión activa
 */

include '../includes/verificar_sesion.php';
include '../config/conexion.php';

// Obtener estadísticas
$con = conexion();
$stats = [];

if ($con) {
    // Total de PQRS
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM pqrs");
    $stats['total'] = mysqli_fetch_assoc($result)['total'] ?? 0;

    // Por estado
    $result = mysqli_query($con, "SELECT estado, COUNT(*) as cantidad FROM pqrs GROUP BY estado");
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['por_estado'][$row['estado']] = $row['cantidad'];
    }

    // PQRS del mes actual
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM pqrs WHERE MONTH(fecha_radicacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_radicacion) = YEAR(CURRENT_DATE())");
    $stats['mes_actual'] = mysqli_fetch_assoc($result)['total'] ?? 0;

    // Vencidas
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM pqrs WHERE fecha_vencimiento < CURDATE() AND estado IN ('PENDIENTE', 'EN_PROCESO')");
    $stats['vencidas'] = mysqli_fetch_assoc($result)['total'] ?? 0;

    // Alertas por urgencia (5, 10, 15 días)
    $result = mysqli_query($con, "SELECT 
        SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 5 AND DATEDIFF(fecha_vencimiento, CURDATE()) >= 0 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as critico,
        SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 6 AND 10 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as urgente,
        SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 11 AND 15 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as moderado
        FROM pqrs WHERE fecha_vencimiento IS NOT NULL");
    $alertas = mysqli_fetch_assoc($result);
    $stats['alertas'] = $alertas;

    // Últimas 5 PQRS
    $result = mysqli_query($con, "SELECT p.*, u.nombre_completo, u.correo_electronico 
                                   FROM pqrs p 
                                   LEFT JOIN usuario u ON p.usuario_id = u.id 
                                   ORDER BY p.fecha_radicacion DESC LIMIT 5");
    $ultimasPQRS = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ultimasPQRS[] = $row;
    }

    mysqli_close($con);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<?php
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
$baseUrl = $isRailway ? '/' : '/PROYECTO_PQRS/';
?>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>css/estilos.css"></head>
<body>
    <?php include '../includes/header.php'; ?>
    <section class="dashboard-section">
        <div class="container">

            <!-- Bienvenida -->
            <div class="dashboard-welcome">
                <div>
                    <h1 class="dashboard-title">
                        <i class="bi bi-speedometer2"></i>
                        Panel de Control
                    </h1>
                    <p class="dashboard-subtitle">
                        Bienvenido, <strong><?php echo htmlspecialchars($adminNombre); ?></strong>. 
                        Gestione las solicitudes PQRS desde aquí.
                    </p>
                </div>
                <div class="dashboard-meta">
                    <span class="dashboard-rol">
                        <i class="bi bi-person-badge"></i>
                        <?php echo htmlspecialchars($adminRol); ?>
                    </span>
                    <span class="dashboard-fecha">
                        <i class="bi bi-calendar3"></i>
                        <?php echo date('d/m/Y'); ?>
                    </span>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="stats-grid">
                <article class="stat-card stat-total">
                    <div class="stat-icon"><i class="bi bi-inbox"></i></div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo $stats['total'] ?? 0; ?></span>
                        <span class="stat-label">Total PQRS</span>
                    </div>
                </article>

                <article class="stat-card stat-pendiente">
                    <div class="stat-icon"><i class="bi bi-clock"></i></div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo $stats['por_estado']['PENDIENTE'] ?? 0; ?></span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                </article>

                <article class="stat-card stat-proceso">
                    <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo $stats['por_estado']['EN_PROCESO'] ?? 0; ?></span>
                        <span class="stat-label">En Proceso</span>
                    </div>
                </article>

                <article class="stat-card stat-resuelto">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo $stats['por_estado']['RESUELTO'] ?? 0; ?></span>
                        <span class="stat-label">Resueltas</span>
                    </div>
                </article>

                <article class="stat-card stat-vencida">
                    <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo $stats['vencidas'] ?? 0; ?></span>
                        <span class="stat-label">Vencidas</span>
                    </div>
                </article>

                <article class="stat-card stat-mes">
                    <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo $stats['mes_actual'] ?? 0; ?></span>
                        <span class="stat-label">Este Mes</span>
                    </div>
                </article>
            </div>

            <!-- Acceso rápido -->
<div class="acceso-rapido-section">
    <div class="dashboard-section-title">
        <h2><i class="bi bi-lightning"></i> Acceso Rápido</h2>
    </div>
    <div class="acceso-grid">
        <a href="pqrs.php" class="acceso-card">  <!-- relativo, mismo directorio -->
            <div class="acceso-icon acceso-inbox">
                <i class="bi bi-inbox-fill"></i>
            </div>
            <span class="acceso-label">Bandeja PQRS</span>
        </a>
        <a href="alertas.php" class="acceso-card">
            <div class="acceso-icon acceso-alertas">
                <i class="bi bi-bell-fill"></i>
            </div>
            <span class="acceso-label">Centro de Alertas</span>
        </a>
        <a href="reportes.php" class="acceso-card">
            <div class="acceso-icon acceso-reportes">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            <span class="acceso-label">Reportes</span>
        </a>
        <a href="#" class="acceso-card" onclick="abrirModalConfig(); return false;">
            <div class="acceso-icon acceso-config">
                <i class="bi bi-gear-fill"></i>
            </div>
            <span class="acceso-label">Mi Perfil</span>
        </a>
    </div>
</div>

            <!-- Últimas solicitudes -->
            <div class="dashboard-section-title">
                <h2><i class="bi bi-clock-history"></i> Últimas Solicitudes</h2>
                <a href="pqrs.php" class="btn btn-sm">Ver todas</a>
            </div>

            <?php if (!empty($ultimasPQRS)): ?>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Asunto</th>
                            <th>Solicitante</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimasPQRS as $pqrs): 
                            $estadoClass = strtolower(str_replace('_', '-', $pqrs['estado']));
                            $tipoLabel = ['peticion'=>'Petición','queja'=>'Queja','reclamo'=>'Reclamo','sugerencia'=>'Sugerencia','denuncia'=>'Denuncia'][$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']);
                        ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code></td>
                            <td>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabel; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(mb_substr($pqrs['asunto'], 0, 40)) . (mb_strlen($pqrs['asunto']) > 40 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></td>
                            <td><span class="estado-tag estado-<?php echo $estadoClass; ?>"><?php echo str_replace('_', ' ', $pqrs['estado']); ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($pqrs['fecha_radicacion'])); ?></td>
                            <td>
                                <div class="acciones-btns">
                                    <a href="pqrs_ver.php?id=<?php echo $pqrs['id']; ?>" class="btn-icon btn-ver" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="pqrs_responder.php?id=<?php echo $pqrs['id']; ?>" class="btn-icon btn-responder" title="Responder">
                                        <i class="bi bi-reply"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="dashboard-empty">
                <i class="bi bi-inbox"></i>
                <p>No hay solicitudes registradas aún.</p>
            </div>
            <?php endif; ?>

        </div>
    </section>
    <!-- ============================================
     MODAL: Configuración de Perfil
     ============================================ -->
<div class="modal-overlay" id="modalConfig">
    <div class="modal-container" style="max-width:520px">
        <div class="modal-header">
            <div>
                <h2 class="modal-title"><i class="bi bi-gear"></i> Mi Perfil</h2>
                <p class="modal-subtitle">Actualice su información personal</p>
            </div>
            <button class="modal-close" onclick="cerrarModalConfig()" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="configMsg" style="display:none;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:14px"></div>
            <form id="formConfig" onsubmit="guardarConfig(event)">
                <div class="config-grupo">
                    <label for="cfg_nombre" class="login-label">
                        <i class="bi bi-person"></i> Nombre Completo
                    </label>
                    <input type="text" id="cfg_nombre" name="nombre_completo" class="login-input"
                        value="<?php echo htmlspecialchars($adminNombre); ?>" required>
                </div>
                <div class="config-grupo">
                    <label for="cfg_correo" class="login-label">
                        <i class="bi bi-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" id="cfg_correo" name="correo_electronico" class="login-input"
                        value="<?php echo htmlspecialchars($adminCorreo); ?>" required>
                </div>
                <hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0">
                <p style="font-size:13px;color:#6b7280;margin-bottom:12px">
                    <i class="bi bi-info-circle"></i> Deje en blanco si no desea cambiar la contraseña
                </p>
                <div class="config-grupo">
                    <label for="cfg_pass_actual" class="login-label">
                        <i class="bi bi-lock"></i> Contraseña Actual
                    </label>
                    <input type="password" id="cfg_pass_actual" name="password_actual" class="login-input"
                        placeholder="Solo si desea cambiarla" autocomplete="current-password">
                </div>
                <div class="config-grupo">
                    <label for="cfg_pass_nueva" class="login-label">
                        <i class="bi bi-key"></i> Nueva Contraseña
                    </label>
                    <input type="password" id="cfg_pass_nueva" name="password_nueva" class="login-input"
                        placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                </div>
                <div class="config-grupo">
                    <label for="cfg_pass_confirm" class="login-label">
                        <i class="bi bi-key-fill"></i> Confirmar Nueva Contraseña
                    </label>
                    <input type="password" id="cfg_pass_confirm" name="password_confirmar" class="login-input"
                        placeholder="Repita la nueva contraseña" autocomplete="new-password">
                </div>
            </form>
        </div>
        <div class="modal-footer" style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalConfig()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="guardarConfig(event)">
                <i class="bi bi-check-lg"></i> Guardar Cambios
            </button>
        </div>
    </div>
</div>

<script>
function abrirModalConfig() {
    document.getElementById('modalConfig').classList.add('active');
    document.getElementById('configMsg').style.display = 'none';
}
function cerrarModalConfig() {
    document.getElementById('modalConfig').classList.remove('active');
}
// Cerrar con ESC o clic fuera
document.getElementById('modalConfig').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalConfig();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModalConfig();
});

function guardarConfig(e) {
    if (e) e.preventDefault();
    var form = document.getElementById('formConfig');
    var msgBox = document.getElementById('configMsg');
    var passNueva = document.getElementById('cfg_pass_nueva').value;
    var passConfirm = document.getElementById('cfg_pass_confirm').value;

    if (passNueva && passNueva.length < 6) {
        mostrarMsg('La nueva contraseña debe tener al menos 6 caracteres.', 'error');
        return;
    }
    if (passNueva && passNueva !== passConfirm) {
        mostrarMsg('Las contraseñas nuevas no coinciden.', 'error');
        return;
    }

    var formData = new FormData(form);
    var btn = document.querySelector('.modal-footer .btn-primary');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Guardando...';

    fetch('actualizar_perfil.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            mostrarMsg(data.msg, 'exito');
            // Actualizar nombre en dashboard
            if (data.nombre) {
                var el = document.querySelector('.dashboard-subtitle strong');
                if (el) el.textContent = data.nombre;
            }
            // Limpiar campos de contraseña
            document.getElementById('cfg_pass_actual').value = '';
            document.getElementById('cfg_pass_nueva').value = '';
            document.getElementById('cfg_pass_confirm').value = '';
        } else {
            mostrarMsg(data.msg, 'error');
        }
    })
    .catch(function() {
        mostrarMsg('Error de conexión. Intente nuevamente.', 'error');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar Cambios';
    });
}

function mostrarMsg(texto, tipo) {
    var box = document.getElementById('configMsg');
    box.style.display = 'block';
    box.textContent = texto;
    if (tipo === 'exito') {
        box.style.background = '#f0fdf4';
        box.style.color = '#065f46';
        box.style.border = '1px solid #bbf7d0';
    } else {
        box.style.background = '#fef2f2';
        box.style.color = '#991b1b';
        box.style.border = '1px solid #fecaca';
    }
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>
</body>
</html>