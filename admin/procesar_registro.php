<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

// Verificar permisos de admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    redirect('../login.php');
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    redirect('registro_usuario.php');
}

// Obtener y validar datos
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? null);
$rol = $_POST['rol'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validaciones básicas
if (empty($nombre) || empty($email) || empty($rol) || empty($password)) {
    $_SESSION['error'] = "Todos los campos requeridos deben completarse";
    redirect('registro_usuario.php');
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Las contraseñas no coinciden";
    redirect('registro_usuario.php');
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres";
    redirect('registro_usuario.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "El email no tiene un formato válido";
    redirect('registro_usuario.php');
}

// Verificar si el email ya existe
try {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "El email ya está registrado";
        redirect('registro_usuario.php');
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error al verificar el email: " . $e->getMessage();
    redirect('registro_usuario.php');
}

// Hash de la contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar nuevo usuario
try {
    $stmt = $conn->prepare("INSERT INTO usuarios 
                          (nombre, email, telefono, password, rol, fecha_registro) 
                          VALUES (?, ?, ?, ?, ?, NOW())");
    
    $stmt->bind_param("sssss", $nombre, $email, $telefono, $password_hash, $rol);
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Usuario registrado exitosamente";
        redirect('gestion_usuarios.php');
    } else {
        $_SESSION['error'] = "Error al registrar el usuario: " . $conn->error;
        redirect('registro_usuario.php');
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error en el registro: " . $e->getMessage();
    redirect('registro_usuario.php');
}
?>