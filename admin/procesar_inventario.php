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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método de solicitud no permitido";
    header("Location: gestion_inventario.php");
    exit();
}

// Obtener y validar datos del formulario
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
$unidad = filter_input(INPUT_POST, 'unidad', FILTER_SANITIZE_STRING);
$fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
$proveedor = filter_input(INPUT_POST, 'proveedor', FILTER_SANITIZE_STRING) ?? null;

// Validaciones
if (empty($nombre) || empty($tipo) || $cantidad === false || $cantidad < 1 || empty($unidad)) {
    $_SESSION['error'] = "Datos del formulario inválidos";
    header("Location: gestion_inventario.php");
    exit();
}

try {
    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO inventario 
                          (nombre, tipo, cantidad, unidad, fecha_vencimiento, proveedor, estado, fecha_ingreso) 
                          VALUES (?, ?, ?, ?, ?, ?, 'disponible', NOW())");
    
    $stmt->bind_param("ssisss", $nombre, $tipo, $cantidad, $unidad, $fecha_vencimiento, $proveedor);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['exito'] = "Item agregado correctamente";
    } else {
        $_SESSION['error'] = "No se pudo agregar el item";
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error al agregar el item: " . $e->getMessage();
}

header("Location: gestion_inventario.php");
exit();
?>