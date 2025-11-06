<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Debes iniciar sesión.";
    header("Location: login.php");
    exit;
}

// Verificar que el usuario es voluntario
if ($_SESSION['usuario']['rol'] !== 'voluntario') {
    $_SESSION['error'] = "Solo los voluntarios pueden registrar animales.";
    header("Location: index.php");
    exit;
}

// Directorio para subir imágenes
$upload_dir = 'uploads/adopciones/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Validar y sanitizar datos
        $nombre = $conn->real_escape_string(trim($_POST['nombre']));
        $especie = $conn->real_escape_string(trim($_POST['especie']));
        $raza = $conn->real_escape_string(trim($_POST['raza']));
        $edad = intval($_POST['edad']);
        $tamano = $conn->real_escape_string(trim($_POST['tamano']));
        $genero = $conn->real_escape_string(trim($_POST['genero']));
        $descripcion = $conn->real_escape_string(trim($_POST['descripcion']));
        $comportamiento = $conn->real_escape_string(trim($_POST['comportamiento']));
        $vacunado = $conn->real_escape_string(trim($_POST['vacunado']));
        $esterilizado = $conn->real_escape_string(trim($_POST['esterilizado']));
        $voluntario_id = $_SESSION['usuario']['id'];
        $estado = 'disponible';

        // Validar archivo de imagen
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Debes subir una foto del animal.");
        }

        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto']['type'];
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Solo se permiten imágenes JPEG, PNG o GIF.");
        }

        // Obtener extensión del archivo
        $file_info = pathinfo($_FILES['foto']['name']);
        $extension = $file_info['extension'];
        $nombre_imagen = uniqid('animal_') . '.' . $extension;
        $ruta_completa = $upload_dir . $nombre_imagen;

        // Mover el archivo
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_completa)) {
            throw new Exception("Error al guardar la imagen.");
        }

        // Insertar en la base de datos
        $stmt = $conn->prepare("
            INSERT INTO animales (
                nombre, especie, raza, edad, tamano, genero, 
                descripcion, foto, estado, fecha_ingreso, voluntario_id, 
                vacunado, esterilizado, comportamiento
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "sssisssssssss",
            $nombre, $especie, $raza, $edad, $tamano, $genero,
            $descripcion, $nombre_imagen, $estado, $voluntario_id,
            $vacunado, $esterilizado, $comportamiento
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar el animal: " . $stmt->error);
        }

        $animal_id = $stmt->insert_id;

        // Registrar en historial
        $conn->query("
            INSERT INTO historial_actualizaciones (tipo, descripcion, fecha) 
            VALUES ('nuevo_animal', 'Se registró el animal: $nombre', NOW())
        ");

        $conn->commit();

        $_SESSION['exito'] = "Animal registrado exitosamente. ¡Ahora aparece en la sección de adopciones!";
        header("Location: mis_adopciones.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        
        // Eliminar imagen si se subió pero hubo error
        if (isset($ruta_completa) && file_exists($ruta_completa)) {
            unlink($ruta_completa);
        }

        $_SESSION['error'] = $e->getMessage();
        header("Location: formulario_dar_adopcion.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Método no permitido";
    header("Location: formulario_dar_adopcion.php");
    exit();
}