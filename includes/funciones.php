<?php
/**
 * Funciones utilitarias del Sistema PQRS
 */

/**
 * Registra una acción en el historial_accion
 * @param string $accion - Tipo de acción (VISUALIZACION, RESPUESTA, CAMBIO_ESTADO, etc.)
 * @param string $descripcion - Descripción detallada
 * @param int $pqrs_id - ID de la PQRS
 * @param int|null $admin_id - ID del administrador (opcional, usa sesión si no se proporciona)
 * @return bool
 */
function registrarAccion($accion, $descripcion, $pqrs_id, $admin_id = null) {
    include_once __DIR__ . '/../config/conexion.php';

    $con = conexion();
    if (!$con) return false;

    // Obtener admin_id de sesión si no se proporciona
    if ($admin_id === null && isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
    }

    // Si aún no hay admin_id, usar NULL (acción del sistema)
    if ($admin_id === null) {
        $admin_id = 0; // o NULL según tu preferencia
    }

    $stmt = $con->prepare("INSERT INTO historial_accion (pqrs_id, administrador_id, accion_realizada, descripcion, fecha_hora) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) return false;

    if ($admin_id === 0) {
        $stmt->bind_param("iiss", $pqrs_id, $admin_id, $accion, $descripcion);
    } else {
        $stmt->bind_param("iiss", $pqrs_id, $admin_id, $accion, $descripcion);
    }

    $result = $stmt->execute();
    $stmt->close();
    mysqli_close($con);

    return $result;
}

/**
 * Obtiene el correo de notificaciones de la configuración del sistema
 * @return string
 */
function obtenerCorreoNotificaciones() {
    include_once __DIR__ . '/../config/conexion.php';

    $con = conexion();
    if (!$con) return 'santiagolizcanosuarez@gmail.com';

    $result = mysqli_query($con, "SELECT correo_notificaciones FROM configuracion_sistema WHERE id = 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $correo = $row['correo_notificaciones'];
        mysqli_close($con);
        return $correo ?: 'santiagolizcanosuarez@gmail.com';
    }
    mysqli_close($con);
    return 'santiagolizcanosuarez@gmail.com';
}

/**
 * Obtiene configuración completa del sistema
 * @return array
 */
function obtenerConfiguracion() {
    include_once __DIR__ . '/../config/conexion.php';

    $con = conexion();
    $config = [];

    if ($con) {
        $result = mysqli_query($con, "SELECT * FROM configuracion_sistema WHERE id = 1");
        if ($result) {
            $config = mysqli_fetch_assoc($result);
        }
        mysqli_close($con);
    }

    return $config;
}