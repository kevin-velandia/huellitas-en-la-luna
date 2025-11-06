<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: perfil.php"); // Cambiado aquí
    exit();
}

// Obtener datos del formulario
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$usuario_id = $_SESSION['usuario']['id'];

// Validaciones
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error'] = "Todos los campos son obligatorios";
    header("Location: perfil.php"); // Cambiado aquí
    exit();
}

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = "Las nuevas contraseñas no coinciden";
    header("Location: perfil.php"); // Cambiado aquí
    exit();
}

if (strlen($new_password) < 6) {
    $_SESSION['error'] = "La nueva contraseña debe tener al menos 6 caracteres";
    header("Location: perfil.php"); // Cambiado aquí
    exit();
}

try {
    $sql = "SELECT password FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "La contraseña actual es incorrecta";
        header("Location: perfil.php"); // Cambiado aquí
        exit();
    }
    
    // Actualizar contraseña
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_password_hash, $usuario_id);
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Contraseña actualizada correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar la contraseña";
    }
    
    header("Location: perfil.php"); // Cambiado aquí
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: perfil.php"); // Cambiado aquí
    exit();
}
?>