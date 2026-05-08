<?php
/**
 * HU-05: Confirmación de Radicación
 * Lee datos reales de la base de datos y muestra estado del correo
 */

require_once '../config/conexion.php';

$pqrs_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pqrs_id === 0) {
    header('Location: ../index.php');
    exit();
}

$con = conexion();
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

$sql = "SELECT p.*, u.tipo_persona, u.nombre_completo, u.documento_identidad,
        u.tipo_documento, u.correo_electronico, u.telefono, u.razon_social,
        u.nit, u.nombre_representante, u.correo_corporativo
        FROM pqrs p
        LEFT JOIN usuario u ON p.usuario_id = u.id
        WHERE p.id = $pqrs_id";

$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ../index.php');
    exit();
}

$pqrs = mysqli_fetch_assoc($result);
mysqli_close($con);

// Verificar estado del correo desde sesión
session_start();
$correoEnviado = isset($_SESSION['correo_enviado']) ? $_SESSION['correo_enviado'] : false;
unset($_SESSION['correo_enviado']); // Limpiar sesión

$nombresTipos = [
    'peticion' => 'Petición',
    'queja' => 'Queja',
    'reclamo' => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia' => 'Denuncia'
];

$nombresPersona = [
    'natural' => 'Persona Natural',
    'juridica' => 'Persona Jurídica',
    'anonima' => 'Anónima'
];

$estados = [
    'PENDIENTE' => ['texto' => 'Pendiente', 'color' => '#059669'],
    'EN_PROCESO' => ['texto' => 'En Proceso', 'color' => '#d97706'],
    'RESUELTO' => ['texto' => 'Resuelto', 'color' => '#1e40af'],
    'RECHAZADO' => ['texto' => 'Rechazado', 'color' => '#dc2626']
];

$estadoActual = $estados[$pqrs['estado']] ?? $estados['PENDIENTE'];

$correo = $pqrs['correo_electronico'] ?? $pqrs['correo_corporativo'] ?? '';
$esAnonima = ($pqrs['tipo_persona'] === 'anonima');

$fechaRadicacion = date('d/m/Y H:i:s', strtotime($pqrs['fecha_radicacion']));
$fechaVencimiento = $pqrs['fecha_vencimiento'] ? date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])) : '15 días hábiles';

$diasRestantes = 0;
if ($pqrs['fecha_vencimiento']) {
    $hoy = new DateTime();
    $vencimiento = new DateTime($pqrs['fecha_vencimiento']);
    $diasRestantes = $hoy->diff($vencimiento)->days;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Radicada - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="confirmacion-page">

    <div class="confirmacion-card">
        
        <div class="confirmacion-header">
            <div class="confirmacion-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <h1 class="confirmacion-titulo">¡Solicitud Radicada Exitosamente!</h1>
            <p class="confirmacion-subtitulo">Su caso ha sido registrado en el sistema</p>
        </div>

        <div class="confirmacion-body">

            <div class="codigo-container">
                <div class="codigo-label">Código de Radicado</div>
                <div class="codigo-valor" id="codigoRadicado"><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></div>
                <div class="codigo-fecha">Radicado el <?php echo $fechaRadicacion; ?></div>
                <button type="button" class="btn-copiar" onclick="copiarCodigo(this)">
                    <i class="bi bi-clipboard"></i>
                    <span>Copiar código</span>
                </button>
            </div>

            <div class="confirmacion-detalles">
                <div class="detalles-titulo">
                    <i class="bi bi-list-check"></i>
                    Resumen de la solicitud
                </div>
                <ul class="detalles-lista">
                    <li>
                        <span class="detalle-label">Tipo de solicitud</span>
                        <span class="detalle-valor"><?php echo $nombresTipos[$pqrs['tipo_solicitud']] ?? 'Petición'; ?></span>
                    </li>
                    <li>
                        <span class="detalle-label">Tipo de persona</span>
                        <span class="detalle-valor"><?php echo $nombresPersona[$pqrs['tipo_persona']] ?? 'Natural'; ?></span>
                    </li>
                    <li>
                        <span class="detalle-label">Estado</span>
                        <span class="detalle-valor" style="color:<?php echo $estadoActual['color']; ?>;font-weight:600;">
                            <i class="bi bi-circle-fill" style="font-size:0.5rem;vertical-align:middle;margin-right:0.25rem;"></i>
                            <?php echo $estadoActual['texto']; ?>
                        </span>
                    </li>
                    <li>
                        <span class="detalle-label">Fecha de vencimiento</span>
                        <span class="detalle-valor"><?php echo $fechaVencimiento; ?></span>
                    </li>
                    <?php if ($diasRestantes > 0): ?>
                    <li>
                        <span class="detalle-label">Días restantes</span>
                        <span class="detalle-valor"><?php echo $diasRestantes; ?> días</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Notificación por correo -->
            <?php if (!empty($correo) && !$esAnonima): ?>
            <div class="notificacion-box" style="<?php echo $correoEnviado ? '' : 'background:#fef3c7;border-color:#fcd34d;'; ?>">
                <i class="bi bi-envelope-check" style="<?php echo $correoEnviado ? '' : 'color:#92400e;'; ?>"></i>
                <p style="<?php echo $correoEnviado ? '' : 'color:#92400e;'; ?>">
                    <?php if ($correoEnviado): ?>
                        <strong>✓ Correo enviado exitosamente:</strong> Se ha enviado confirmación al correo <strong><?php echo htmlspecialchars($correo); ?></strong>. Revise su bandeja de entrada.
                    <?php else: ?>
                        <strong>⚠ Notificación pendiente:</strong> El sistema intentará enviar confirmación al correo <strong><?php echo htmlspecialchars($correo); ?></strong>. Si no lo recibe en los próximos minutos, guarde su código de radicado para consultar el estado.
                    <?php endif; ?>
                </p>
            </div>
            <?php elseif ($esAnonima): ?>
            <div class="notificacion-box" style="background:#fef3c7;border-color:#fcd34d;">
                <i class="bi bi-exclamation-triangle" style="color:#92400e;"></i>
                <p style="color:#92400e;">
                    <strong>Solicitud anónima:</strong> Guarde su código de radicado para consultar el estado. No se enviarán notificaciones por correo.
                </p>
            </div>
            <?php endif; ?>

            <div class="confirmacion-actions">
                <a href="consulta_pqrs.php" class="btn-secundario">
                    <i class="bi bi-search"></i>
                    Consultar Estado
                </a>
                <a href="../index.php" class="btn-principal">
                    <i class="bi bi-house"></i>
                    Volver al Inicio
                </a>
            </div>

        </div>

    </div>

    <script>
    function copiarCodigo(boton) {
        const codigo = document.getElementById('codigoRadicado').textContent;
        
        navigator.clipboard.writeText(codigo).then(function() {
            boton.classList.add('copiado');
            boton.innerHTML = '<i class="bi bi-check-lg"></i><span>¡Copiado!</span>';
            
            setTimeout(function() {
                boton.classList.remove('copiado');
                boton.innerHTML = '<i class="bi bi-clipboard"></i><span>Copiar código</span>';
            }, 2000);
        }).catch(function() {
            const textarea = document.createElement('textarea');
            textarea.value = codigo;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            boton.classList.add('copiado');
            boton.innerHTML = '<i class="bi bi-check-lg"></i><span>¡Copiado!</span>';
        });
    }
    </script>

</body>
</html>