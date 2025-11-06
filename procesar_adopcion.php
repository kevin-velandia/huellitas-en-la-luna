<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';

if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Debes iniciar sesión para adoptar.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: adopciones.php");
    exit();
}

try {
    $conn->begin_transaction();

    // Validar datos
    $animal_id = (int)$_POST['animal_id'];
    $usuario_id = (int)$_POST['usuario_id'];
    $motivo = $conn->real_escape_string(trim($_POST['motivo']));
    $experiencia = $conn->real_escape_string(trim($_POST['experiencia']));
    $hogar = $conn->real_escape_string(trim($_POST['hogar']));
    $metodo_contacto = $conn->real_escape_string(trim($_POST['metodo_contacto']));

    if (empty($motivo) || empty($experiencia) || empty($hogar)) {
        throw new Exception("Todos los campos son obligatorios");
    }

    // Insertar solicitud
    $stmt = $conn->prepare("
        INSERT INTO solicitudes_adopcion (
            animal_id, usuario_id, motivo, experiencia, hogar, 
            fecha_solicitud, estado, metodo_contacto
        ) VALUES (?, ?, ?, ?, ?, NOW(), 'pendiente', ?)
    ");
    $stmt->bind_param("iissss", $animal_id, $usuario_id, $motivo, $experiencia, $hogar, $metodo_contacto);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al registrar la solicitud: " . $stmt->error);
    }

    // Actualizar estado del animal (opcional, depende de tu flujo)
    $stmt = $conn->prepare("UPDATE animales SET estado = 'en_proceso' WHERE id = ?");
    $stmt->bind_param("i", $animal_id);
    $stmt->execute();

    $conn->commit();

    $_SESSION['exito'] = "Solicitud de adopción enviada correctamente";
    header("Location: mis_adopciones.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}