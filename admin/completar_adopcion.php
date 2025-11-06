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
if (!isset($_POST['solicitud_id'])) {
    $_SESSION['error'] = "Solicitud no especificada.";
    header("Location: gestion_adopciones.php");
    exit();
}

$solicitud_id = (int)$_POST['solicitud_id'];
$tipo = $_POST['tipo'] ?? 'completado';
$descripcion = $_POST['descripcion'] ?? 'Adopción completada mediante confirmación directa';
$responsable_id = $_SESSION['usuario']['id']; // Usar el ID del usuario actual

try {
    // Verificar que el responsable_id existe en la tabla usuarios
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $responsable_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("El usuario responsable no existe en la base de datos");
    }

    // Registrar el seguimiento
    $stmt = $conn->prepare("
        INSERT INTO seguimiento_adopciones 
        (solicitud_id, tipo, descripcion, fecha, responsable_id) 
        VALUES (?, ?, ?, NOW(), ?)
    ");
    $stmt->bind_param("issi", $solicitud_id, $tipo, $descripcion, $responsable_id);
    $stmt->execute();

    // Actualizar estado de la solicitud
    $stmt = $conn->prepare("
        UPDATE solicitudes_adopcion 
        SET estado = 'completada', fecha_resolucion = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();

    // Actualizar estado del animal
    $stmt = $conn->prepare("
        UPDATE animales a
        JOIN solicitudes_adopcion s ON a.id = s.animal_id
        SET a.estado = 'adoptado'
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();

    $_SESSION['exito'] = "Adopción marcada como completada correctamente.";
    header("Location: ver_solicitud.php?id=".$solicitud_id);
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = "Error al completar la adopción: " . $e->getMessage();
    header("Location: ver_solicitud.php?id=".$solicitud_id);
    exit();
}
?>