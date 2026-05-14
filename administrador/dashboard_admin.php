<?php
/* HU-Dashboard: Panel de Control del Administrador */
include '../includes/verificar_sesion.php';
include '../config/conexion.php';

$con = conexion();
$stats = [];
$ultimasPQRS = [];

if ($con) {
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM pqrs");
    $stats['total'] = mysqli_fetch_assoc($result)['total'] ?? 0;

    $result = mysqli_query($con, "SELECT estado, COUNT(*) as cantidad FROM pqrs GROUP BY estado");
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['por_estado'][$row['estado']] = $row['cantidad'];
    }

    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM pqrs WHERE MONTH(fecha_radicacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_radicacion) = YEAR(CURRENT_DATE())");
    $stats['mes_actual'] = mysqli_fetch_assoc($result)['total'] ?? 0;

    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM pqrs WHERE fecha_vencimiento < CURDATE() AND estado IN ('PENDIENTE', 'EN_PROCESO')");
    $stats['vencidas'] = mysqli_fetch_assoc($result)['total'] ?? 0;

    $result = mysqli_query($con, "SELECT p.*, u.nombre_completo, u.correo_electronico 
                                   FROM pqrs p 
                                   LEFT JOIN usuario u ON p.usuario_id = u.id 
                                   ORDER BY p.fecha_radicacion DESC LIMIT 5");
    while ($row = mysqli_fetch_assoc($result)) {
        $ultimasPQRS[] = $row;
    }

    mysqli_close($con);
}

// Incluir head (define $basePath)
include '../includes/head.php';
include '../includes/header.php';
?>

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
                    Bienvenido, <strong><?php echo htmlspecialchars($adminNombre ?? 'Admin'); ?></strong>. 
                    Gestione las solicitudes PQRS desde aquí.
                </p>
            </div>
            <div class="dashboard-meta">
                <span class="dashboard-rol">
                    <i class="bi bi-person-badge"></i>
                    <?php echo htmlspecialchars($adminRol ?? 'Administrador'); ?>
                </span>
                <span class="dashboard-fecha">
                    <i class="bi bi-calendar3"></i>
                    <?php echo date('d/m/Y'); ?>
                </span>
            </div>
        </div>

        <!-- Estadísticas -->
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
                <a href="pqrs.php" class="acceso-card">
                    <div class="acceso-icon acceso-inbox"><i class="bi bi-inbox-fill"></i></div>
                    <span class="acceso-label">Bandeja PQRS</span>
                </a>
                <a href="alertas.php" class="acceso-card">
                    <div class="acceso-icon acceso-alertas"><i class="bi bi-bell-fill"></i></div>
                    <span class="acceso-label">Centro de Alertas</span>
                </a>
                <a href="reportes.php" class="acceso-card">
                    <div class="acceso-icon acceso-reportes"><i class="bi bi-bar-chart-fill"></i></div>
                    <span class="acceso-label">Reportes</span>
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
                        <td><span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>"><?php echo $tipoLabel; ?></span></td>
                        <td><?php echo htmlspecialchars(mb_substr($pqrs['asunto'], 0, 40)) . (mb_strlen($pqrs['asunto']) > 40 ? '...' : ''); ?></td>
                        <td><?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></td>
                        <td><span class="estado-tag estado-<?php echo $estadoClass; ?>"><?php echo str_replace('_', ' ', $pqrs['estado']); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($pqrs['fecha_radicacion'])); ?></td>
                        <td>
                            <div class="acciones-btns">
                                <a href="pqrs_ver.php?id=<?php echo $pqrs['id']; ?>" class="btn-icon btn-ver" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                <a href="pqrs_responder.php?id=<?php echo $pqrs['id']; ?>" class="btn-icon btn-responder" title="Responder"><i class="bi bi-reply"></i></a>
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

<?php include '../includes/footer.php'; ?>