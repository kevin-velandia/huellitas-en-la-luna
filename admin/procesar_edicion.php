<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';

// Verificar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Validar datos recibidos
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error'] = "Solicitud inválida";
    header("Location: gestion_inventario.php");
    exit();
}

// Procesar los datos del formulario
$id = intval($_POST['id']);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
$unidad = filter_input(INPUT_POST, 'unidad', FILTER_SANITIZE_STRING);
$fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
$proveedor = filter_input(INPUT_POST, 'proveedor', FILTER_SANITIZE_STRING) ?? null;
$estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

// Validaciones adicionales
if (empty($nombre) || empty($tipo) || $cantidad === false || $cantidad < 0 || empty($unidad) || empty($estado)) {
    $_SESSION['error'] = "Datos del formulario inválidos";
    header("Location: gestion_inventario.php");
    exit();
}

try {
    // Actualizar en la base de datos
    $stmt = $conn->prepare("UPDATE inventario SET 
                          nombre = ?, 
                          tipo = ?, 
                          cantidad = ?, 
                          unidad = ?, 
                          fecha_vencimiento = ?, 
                          proveedor = ?, 
                          estado = ?
                          WHERE id = ?");
    
    $stmt->bind_param("ssissssi", $nombre, $tipo, $cantidad, $unidad, $fecha_vencimiento, $proveedor, $estado, $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['exito'] = "Item actualizado correctamente";
    } else {
        $_SESSION['error'] = "No se realizaron cambios o el item no existe";
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error al actualizar el item: " . $e->getMessage();
}

header("Location: gestion_inventario.php");
exit();
?>