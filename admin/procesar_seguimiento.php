<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

session_start();

// Verificar permisos de admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['solicitud_id']) || !isset($_POST['tipo']) || !isset($_POST['descripcion'])) {
    $_SESSION['error'] = "Datos incompletos para el seguimiento.";
    header("Location: gestion_adopciones.php");
    exit();
}

$solicitud_id = (int)$_POST['solicitud_id'];
$tipo = $_POST['tipo'];
$descripcion = $_POST['descripcion'];
$admin_id = $_SESSION['usuario']['id'];

try {
    // Insertar el nuevo seguimiento
    $stmt = $conn->prepare("
        INSERT INTO seguimiento_adopciones 
        (solicitud_id, tipo, descripcion, fecha, admin_id) 
        VALUES (?, ?, ?, NOW(), ?)
    ");
    $stmt->bind_param("issi", $solicitud_id, $tipo, $descripcion, $admin_id);
    $stmt->execute();

    // Si es un completado, actualizar el estado
    if ($tipo == 'completado') {
        $stmt = $conn->prepare("
            UPDATE solicitudes_adopcion 
            SET estado = 'completada', fecha_resolucion = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();

        $stmt = $conn->prepare("
            UPDATE animales a
            JOIN solicitudes_adopcion s ON a.id = s.animal_id
            SET a.estado = 'adoptado'
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
    }

    $_SESSION['exito'] = "Seguimiento registrado correctamente.";
    header("Location: ver_solicitud.php?id=".$solicitud_id);
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Error al registrar el seguimiento: " . $e->getMessage();
    header("Location: ver_solicitud.php?id=".$solicitud_id);
    exit();
}
?>