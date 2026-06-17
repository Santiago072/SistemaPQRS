<?php
/**
 * HU-Config: Actualizar perfil del administrador (AJAX)
 * Procesa la actualización de nombre, correo y contraseña del admin logueado
 */

include __DIR__ . '/../layouts/verificar_sesion.php';
include __DIR__ . '/../../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido.']);
    exit();
}

$nombre  = trim($_POST['nombre_completo'] ?? '');
$correo  = trim($_POST['correo_electronico'] ?? '');
$passActual = $_POST['password_actual'] ?? '';
$passNueva  = $_POST['password_nueva'] ?? '';
$passConfirm = $_POST['password_confirmar'] ?? '';

// Validaciones
if (empty($nombre) || empty($correo)) {
    echo json_encode(['ok' => false, 'msg' => 'Nombre y correo son obligatorios.']);
    exit();
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'msg' => 'Correo electrónico no válido.']);
    exit();
}

$con = conexion();
if (!$con) {
    echo json_encode(['ok' => false, 'msg' => 'Error de conexión con la base de datos.']);
    exit();
}

// Si quiere cambiar contraseña, validar
$cambiarPass = false;
if (!empty($passNueva)) {
    if (empty($passActual)) {
        echo json_encode(['ok' => false, 'msg' => 'Debe ingresar su contraseña actual para cambiarla.']);
        mysqli_close($con);
        exit();
    }
    if (strlen($passNueva) < 6) {
        echo json_encode(['ok' => false, 'msg' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
        mysqli_close($con);
        exit();
    }
    if ($passNueva !== $passConfirm) {
        echo json_encode(['ok' => false, 'msg' => 'Las contraseñas nuevas no coinciden.']);
        mysqli_close($con);
        exit();
    }

    // Verificar contraseña actual
    $stmt = mysqli_prepare($con, "SELECT contrasena FROM administrador WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $adminId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) {
        echo json_encode(['ok' => false, 'msg' => 'Administrador no encontrado.']);
        mysqli_close($con);
        exit();
    }

    $passOk = ($passActual === $row['contrasena']) || password_verify($passActual, $row['contrasena']);
    if (!$passOk) {
        echo json_encode(['ok' => false, 'msg' => 'La contraseña actual es incorrecta.']);
        mysqli_close($con);
        exit();
    }

    $cambiarPass = true;
}

// Actualizar datos
if ($cambiarPass) {
    $hashNueva = password_hash($passNueva, PASSWORD_BCRYPT);
    $stmt = mysqli_prepare($con, "UPDATE administrador SET nombre_completo = ?, correo_electronico = ?, contrasena = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $correo, $hashNueva, $adminId);
} else {
    $stmt = mysqli_prepare($con, "UPDATE administrador SET nombre_completo = ?, correo_electronico = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $nombre, $correo, $adminId);
}

if ($stmt && mysqli_stmt_execute($stmt)) {
    // Actualizar sesión
    $_SESSION['admin_nombre'] = $nombre;
    $_SESSION['admin_correo'] = $correo;

    mysqli_stmt_close($stmt);
    mysqli_close($con);
    echo json_encode(['ok' => true, 'msg' => 'Perfil actualizado correctamente.', 'nombre' => $nombre]);
} else {
    mysqli_close($con);
    echo json_encode(['ok' => false, 'msg' => 'Error al actualizar. Intente nuevamente.']);
}
