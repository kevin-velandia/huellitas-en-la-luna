<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/database.php'; // Aquí obtienes $conn

$usuario_id = $_SESSION['usuario']['id'];
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

$errores = '';
$foto_nombre = $_SESSION['usuario']['foto_perfil'] ?? 'default.jpg';

// Validación básica
if (!$nombre || !$email) {
    $errores = "El nombre y el email son obligatorios.";
}

// Procesar imagen si se sube una
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $foto_original = $_FILES['foto']['name'];
    $ext = strtolower(pathinfo($foto_original, PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $permitidas)) {
        if ($_FILES['foto']['size'] <= 2 * 1024 * 1024) {
            $nuevo_nombre = 'perfil_' . time() . '.' . $ext;
            $ruta_destino = '../assets/img/perfiles/' . $nuevo_nombre;

            if (move_uploaded_file($foto_tmp, $ruta_destino)) {
                $foto_nombre = $nuevo_nombre;
            } else {
                $errores = "No se pudo guardar la imagen.";
            }
        } else {
            $errores = "La imagen no debe superar los 2MB.";
        }
    } else {
        $errores = "Formato de imagen no permitido. Usa JPG, PNG o GIF.";
    }
}

if ($errores) {
    $_SESSION['error'] = $errores;
    header("Location: editar_perfil.php");
    exit();
}

// Aquí ya $conn está definido porque viene de database.php
$sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, foto_perfil = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $nombre, $email, $telefono, $foto_nombre, $usuario_id);

if ($stmt->execute()) {
    // Actualizar datos en sesión
    $_SESSION['usuario']['nombre'] = $nombre;
    $_SESSION['usuario']['email'] = $email;
    $_SESSION['usuario']['telefono'] = $telefono;
    $_SESSION['usuario']['foto_perfil'] = $foto_nombre;

    $_SESSION['exito'] = "Perfil actualizado correctamente.";
} else {
    $_SESSION['error'] = "Error al actualizar el perfil.";
}

header("Location: editar_perfil.php");
exit();
