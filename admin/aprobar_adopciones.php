<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

session_start();

// Verificar rol admin y que se recibe id válido
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin' || !isset($_GET['id'])) {
    $_SESSION['error'] = "Acceso no autorizado o solicitud inválida.";
    header("Location: gestion_adopciones.php");
    exit();
}

$solicitud_id = (int)$_GET['id'];

try {
    $conn->begin_transaction();

    // Verificar si existe la columna fecha_resolucion
    $column_check = $conn->query("SHOW COLUMNS FROM solicitudes_adopcion LIKE 'fecha_resolucion'");
    $tiene_fecha_resolucion = ($column_check->num_rows > 0);
    
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
    $voluntario_id = $solicitud['voluntario_id'];
    $solicitante_id = $solicitud['usuario_id'];
    $stmt->close();

    // Actualizar la solicitud a aprobada (versión adaptable)
    $sql_update = "
        UPDATE solicitudes_adopcion 
        SET estado = 'aprobada', 
            admin_id = ?";
    
    if ($tiene_fecha_resolucion) {
        $sql_update .= ", fecha_resolucion = NOW()";
    }
    
    $sql_update .= " WHERE id = ?";
    
    $stmt = $conn->prepare($sql_update);
    if (!$stmt) throw new Exception("Error en actualización solicitud: " . $conn->error);
    $stmt->bind_param("ii", $_SESSION['usuario']['id'], $solicitud_id);
    $stmt->execute();
    $stmt->close();

    // Resto del código permanece igual...
    // [Aquí iría el resto de tu código actual para actualizar el animal, rechazar otras solicitudes, etc.]

    $conn->commit();

    $_SESSION['exito'] = "Adopción aprobada correctamente. Se han enviado las notificaciones correspondientes.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error al aprobar la adopción: " . $e->getMessage();
}

header("Location: gestion_adopciones.php");
exit();