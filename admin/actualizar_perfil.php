<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/config.php'; // archivo con conexión ($conn)
require_once 'includes/database.php'; // si tienes funciones adicionales

$usuario_id = $_SESSION['usuario']['id'] ?? null;

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

$errores = '';
$foto_nombre = $_SESSION['usuario']['foto_perfil'] ?? 'default.jpg';

// Validaciones básicas
if (!$nombre || !$email) {
    $errores = "El nombre y el correo electrónico son obligatorios.";
}

// Procesar la foto si se subió una nueva
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $foto_original = $_FILES['foto']['name'];
    $ext = pathinfo($foto_original, PATHINFO_EXTENSION);
    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array(strtolower($ext), $permitidas)) {
        if ($_FILES['foto']['size'] <= 2 * 1024 * 1024) { // 2MB
            $nuevo_nombre = 'perfil_' . time() . '.' . $ext;
            $ruta_destino = 'assets/img/perfiles/' . $nuevo_nombre;

            if (move_uploaded_file($foto_tmp, $ruta_destino)) {
                $foto_nombre = $nuevo_nombre;
            } else {
                $errores = "No se pudo guardar la nueva foto.";
            }
        } else {
            $errores = "La imagen no debe superar los 2MB.";
        }
    } else {
        $errores = "Formato de imagen no permitido. Usa JPG, PNG o GIF.";
    }
}

// Si hay errores, redirigir con mensaje
if ($errores) {
    $_SESSION['error'] = $errores;
    $_SESSION['old'] = $_POST;
    header("Location: editar_perfil.php");
    exit();
}

// Actualizar en la base de datos
$sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, foto_perfil = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $nombre, $email, $telefono, $foto_nombre, $usuario_id);

if ($stmt->execute()) {
    // Actualizar sesión
    $_SESSION['usuario']['nombre'] = $nombre;
    $_SESSION['usuario']['email'] = $email;
    $_SESSION['usuario']['telefono'] = $telefono;
    $_SESSION['usuario']['foto_perfil'] = $foto_nombre;

    $_SESSION['exito'] = "Perfil actualizado con éxito.";
} else {
    $_SESSION['error'] = "Error al actualizar el perfil.";
}

header("Location: editar_perfil.php");
exit();
