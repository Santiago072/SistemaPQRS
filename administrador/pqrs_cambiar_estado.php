<?php
/* HU-Detalle y Respuesta: Cambio rápido de estado de PQRS */

include '../includes/verificar_sesion.php';
include '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pqrs.php');
    exit;
}

$pqrs_id = intval($_POST['pqrs_id'] ?? 0);
$nuevo_estado = $_POST['nuevo_estado'] ?? '';
$comentario = trim($_POST['comentario'] ?? '');
$redirect = $_POST['redirect'] ?? 'pqrs.php';

// Validar
$estados_validos = ['PENDIENTE', 'EN_PROCESO', 'RESUELTO', 'RECHAZADO'];

if (!$pqrs_id || !in_array($nuevo_estado, $estados_validos)) {
    header("Location: $redirect&error=invalid");
    exit;
}

$con = conexion();

// Obtener estado actual
$stmt = $con->prepare("SELECT estado, codigo_radicado FROM pqrs WHERE id = ?");
$stmt->bind_param("i", $pqrs_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    mysqli_close($con);
    header("Location: $redirect&error=not_found");
    exit;
}

$pqrs = $result->fetch_assoc();
$estado_anterior = $pqrs['estado'];

// Validar transición de estado - HU-Detalle: Pendiente → En proceso → Resuelto/Rechazado
$transiciones_validas = [
    'PENDIENTE' => ['EN_PROCESO', 'RESUELTO', 'RECHAZADO'],
    'EN_PROCESO' => ['RESUELTO', 'RECHAZADO'],
    'RESUELTO' => [],
    'RECHAZADO' => []
];

if (!in_array($nuevo_estado, $transiciones_validas[$estado_anterior] ?? [])) {
    mysqli_close($con);
    header("Location: $redirect&error=invalid_transition");
    exit;
}

// Actualizar estado
$stmt = $con->prepare("UPDATE pqrs SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?");
$stmt->bind_param("si", $nuevo_estado, $pqrs_id);

if ($stmt->execute()) {
    // Registrar en historial_accion (tabla correcta según SQL)
    $descripcion = "Estado cambiado de '$estado_anterior' a '$nuevo_estado'";
    if ($comentario) {
        $descripcion .= ". Comentario: $comentario";
    }
    
    $stmt_log = $con->prepare("INSERT INTO historial_accion (pqrs_id, administrador_id, accion_realizada, estado_anterior, estado_nuevo, descripcion, fecha_hora) VALUES (?, ?, 'CAMBIO_ESTADO', ?, ?, ?, NOW())");
    $stmt_log->bind_param("iisss", $pqrs_id, $adminId, $estado_anterior, $nuevo_estado, $descripcion);
    $stmt_log->execute();
    
    mysqli_close($con);
    
    // Redirigir con éxito
    $separator = strpos($redirect, '?') !== false ? '&' : '?';
    header("Location: {$redirect}{$separator}success=estado_actualizado");
    exit;
} else {
    mysqli_close($con);
    header("Location: $redirect&error=update_failed");
    exit;
}
?>