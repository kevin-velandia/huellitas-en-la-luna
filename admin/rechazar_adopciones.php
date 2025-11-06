<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

session_start();

// Verificar que el usuario es admin y que se recibe un ID válido
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin' || !isset($_GET['id'])) {
    $_SESSION['error'] = "Acceso no autorizado o solicitud inválida.";
    header("Location: gestion_adopciones.php");
    exit();
}

$solicitud_id = (int) $_GET['id'];

try {
    $conn->begin_transaction();

    // Obtener información completa de la solicitud
    $stmt = $conn->prepare("
        SELECT s.*, a.nombre AS nombre_animal, a.voluntario_id, 
               u.nombre AS nombre_solicitante, u.email AS email_solicitante
        FROM solicitudes_adopcion s
        JOIN animales a ON a.id = s.animal_id
        JOIN usuarios u ON u.id = s.usuario_id
        WHERE s.id = ?
    ");
    if (!$stmt) throw new Exception("Error en preparación: " . $conn->error);
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Solicitud no encontrada.");
    }

    $solicitud = $result->fetch_assoc();
    $animal_id = $solicitud['animal_id'];
    $animal_nombre = $solicitud['nombre_animal'];
    $solicitante_id = $solicitud['usuario_id'];
    $stmt->close();

    // Actualizar la solicitud a rechazada
    $stmt = $conn->prepare("
        UPDATE solicitudes_adopcion 
        SET estado = 'rechazado', 
            fecha_resolucion = NOW(),
            admin_id = ?
        WHERE id = ?
    ");
    if (!$stmt) throw new Exception("Error en actualización solicitud: " . $conn->error);
    $stmt->bind_param("ii", $_SESSION['usuario']['id'], $solicitud_id);
    $stmt->execute();
    $stmt->close();

    // Verificar si quedan solicitudes pendientes para el mismo animal
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS pendientes 
        FROM solicitudes_adopcion 
        WHERE animal_id = ? AND estado = 'pendiente'
    ");
    if (!$stmt) throw new Exception("Error en verificación de pendientes: " . $conn->error);
    $stmt->bind_param("i", $animal_id);
    $stmt->execute();
    $pendientes = $stmt->get_result()->fetch_assoc()['pendientes'];
    $stmt->close();

    // Si no quedan pendientes, actualizar estado del animal a disponible
    if ($pendientes == 0) {
        $stmt = $conn->prepare("
            UPDATE animales 
            SET estado = 'disponible',
                adoptado_por = NULL
            WHERE id = ?
        ");
        if (!$stmt) throw new Exception("Error en actualización animal: " . $conn->error);
        $stmt->bind_param("i", $animal_id);
        $stmt->execute();
        $stmt->close();
    }

    // Notificar al solicitante
    $mensaje_solicitante = $conn->real_escape_string(
        "Lamentamos informarte que tu solicitud para adoptar a '$animal_nombre' ha sido rechazada."
    );
    
    $stmt = $conn->prepare("
        INSERT INTO notificaciones (usuario_id, tipo, mensaje, fecha) 
        VALUES (?, 'adopcion_rechazada', ?, NOW())
    ");
    if (!$stmt) throw new Exception("Error al notificar solicitante: " . $conn->error);
    $stmt->bind_param("is", $solicitante_id, $mensaje_solicitante);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    $_SESSION['exito'] = "Solicitud rechazada correctamente. " . 
                         ($pendientes == 0 ? "El animal ha vuelto a estar disponible." : "");
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error al rechazar la solicitud: " . $e->getMessage();
}

header("Location: gestion_adopciones.php");
exit();