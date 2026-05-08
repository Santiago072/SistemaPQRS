<?php
/* HU-Alertas y Vencimiento: Envío de alertas por correo electrónico */

include '../includes/verificar_sesion.php';
include '../config/conexion.php';

$con = conexion();

$mensaje_exito = '';
$mensaje_error = '';
$alertas_enviadas = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_alerta = $_POST['tipo_alerta'] ?? 'todas';
    $email_destino = trim($_POST['email_destino'] ?? $adminEmail);
    
    // Obtener PQRS según el tipo de alerta
    $where_dias = match($tipo_alerta) {
        'vencidas' => "DATEDIFF(p.fecha_vencimiento, CURDATE()) < 0",
        'criticas' => "DATEDIFF(p.fecha_vencimiento, CURDATE()) BETWEEN 0 AND 5",
        'urgentes' => "DATEDIFF(p.fecha_vencimiento, CURDATE()) BETWEEN 6 AND 10",
        'moderadas' => "DATEDIFF(p.fecha_vencimiento, CURDATE()) BETWEEN 11 AND 15",
        default => "DATEDIFF(p.fecha_vencimiento, CURDATE()) <= 15"
    };
    
    $query = "SELECT p.*, u.nombre_completo,
                     DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
              FROM pqrs p 
              LEFT JOIN usuario u ON p.usuario_id = u.id 
              WHERE p.estado IN ('PENDIENTE', 'EN_PROCESO')
              AND p.fecha_vencimiento IS NOT NULL
              AND $where_dias
              ORDER BY p.fecha_vencimiento ASC";
    
    $result = mysqli_query($con, $query);
    $pqrs_alertas = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $pqrs_alertas[] = $row;
    }
    
    if (count($pqrs_alertas) > 0) {
        // Construir contenido del email
        $asunto = "Alerta PQRS - " . count($pqrs_alertas) . " solicitudes requieren atención";
        
        $cuerpo = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='font-family: Arial, sans-serif;'>";
        $cuerpo .= "<h2 style='color: #1e40af;'>Sistema PQRS - Alerta de Vencimientos</h2>";
        $cuerpo .= "<p>Fecha del reporte: " . date('d/m/Y H:i') . "</p>";
        $cuerpo .= "<p>Las siguientes solicitudes PQRS requieren su atención:</p>";
        
        $cuerpo .= "<table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>";
        $cuerpo .= "<tr style='background: #1e40af; color: white;'>";
        $cuerpo .= "<th style='padding: 10px; border: 1px solid #ddd;'>Código</th>";
        $cuerpo .= "<th style='padding: 10px; border: 1px solid #ddd;'>Tipo</th>";
        $cuerpo .= "<th style='padding: 10px; border: 1px solid #ddd;'>Asunto</th>";
        $cuerpo .= "<th style='padding: 10px; border: 1px solid #ddd;'>Vencimiento</th>";
        $cuerpo .= "<th style='padding: 10px; border: 1px solid #ddd;'>Estado</th>";
        $cuerpo .= "</tr>";
        
        foreach ($pqrs_alertas as $pqrs) {
            $dias = $pqrs['dias_restantes'];
            $color_fila = $dias < 0 ? '#fee2e2' : ($dias <= 5 ? '#fef3c7' : ($dias <= 10 ? '#dbeafe' : '#d1fae5'));
            $estado_dias = $dias < 0 ? 'VENCIDA' : "$dias días";
            
            $cuerpo .= "<tr style='background: $color_fila;'>";
            $cuerpo .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($pqrs['codigo_radicado']) . "</td>";
            $cuerpo .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . ucfirst($pqrs['tipo_solicitud']) . "</td>";
            $cuerpo .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars(mb_substr($pqrs['asunto'], 0, 50)) . "</td>";
            $cuerpo .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])) . " ($estado_dias)</td>";
            $cuerpo .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . $pqrs['estado'] . "</td>";
            $cuerpo .= "</tr>";
        }
        
        $cuerpo .= "</table>";
        $cuerpo .= "<p style='margin-top: 20px;'>Por favor, acceda al sistema para gestionar estas solicitudes.</p>";
        $cuerpo .= "<p style='color: #666; font-size: 12px;'>Este es un mensaje automático del Sistema PQRS.</p>";
        $cuerpo .= "</body></html>";
        
        // Enviar email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Sistema PQRS <noreply@sistema-pqrs.com>\r\n";
        
        if (mail($email_destino, $asunto, $cuerpo, $headers)) {
            $alertas_enviadas = count($pqrs_alertas);
            $mensaje_exito = "Se enviaron $alertas_enviadas alertas al correo $email_destino";
            
            // Registrar en log (sin pqrs_id específico, solo descripción)
            // No podemos usar historial_accion porque requiere pqrs_id
        } else {
            $mensaje_error = "Error al enviar el correo. Verifique la configuración del servidor.";
        }
    } else {
        $mensaje_exito = "No hay PQRS que cumplan con los criterios de alerta seleccionados.";
    }
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Alertas por Email - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="dashboard-section">
        <div class="container">
            <div class="detalle-nav">
                <a href="alertas.php" class="btn-volver-detalle">
                    <i class="bi bi-arrow-left"></i>
                    Volver a Alertas
                </a>
            </div>

            <div class="dashboard-welcome">
                <div>
                    <h1 class="dashboard-title">
                        <i class="bi bi-envelope-fill"></i>
                        Enviar Alertas por Email
                    </h1>
                    <p class="dashboard-subtitle">
                        Configure y envíe alertas de vencimiento al equipo
                    </p>
                </div>
            </div>

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

            <div class="detalle-card" style="max-width: 600px;">
                <div class="detalle-card-header">
                    <h2><i class="bi bi-gear"></i> Configurar Envío</h2>
                </div>
                <div class="detalle-card-body">
                    <form method="POST" action="" class="envio-alertas-form">
                        <div class="form-grupo">
                            <label class="form-label">
                                <i class="bi bi-funnel"></i>
                                Tipo de Alerta
                            </label>
                            <select name="tipo_alerta" class="form-select" required>
                                <option value="todas">Todas las alertas (15 días o menos)</option>
                                <option value="vencidas">Solo PQRS Vencidas</option>
                                <option value="criticas">Solo Críticas (0-5 días)</option>
                                <option value="urgentes">Solo Urgentes (6-10 días)</option>
                                <option value="moderadas">Solo Moderadas (11-15 días)</option>
                            </select>
                        </div>
                        
                        <div class="form-grupo">
                            <label class="form-label">
                                <i class="bi bi-envelope"></i>
                                Correo de Destino
                            </label>
                            <input type="email" name="email_destino" class="form-input" 
                                   value="<?php echo htmlspecialchars($adminEmail); ?>" required>
                            <p class="form-ayuda">El reporte de alertas se enviará a este correo.</p>
                        </div>
                        
                        <div class="form-actions-responder">
                            <a href="alertas.php" class="btn-volver">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn-enviar">
                                <i class="bi bi-send"></i>
                                Enviar Alertas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>