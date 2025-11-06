<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';

// Verificar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Verificar que se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de item inválido";
    header("Location: gestion_inventario.php");
    exit();
}

$id = intval($_GET['id']);

try {
    // Eliminar el item de la base de datos
    $stmt = $conn->prepare("DELETE FROM inventario WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['exito'] = "Item eliminado correctamente";
    } else {
        $_SESSION['error'] = "No se encontró el item o ya fue eliminado";
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error al eliminar el item: " . $e->getMessage();
}

header("Location: gestion_inventario.php");
exit();
?>
