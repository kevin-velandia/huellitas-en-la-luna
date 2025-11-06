<?php
require_once 'database.php';

// Funciones generales
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// Funciones específicas de PetLove
function registrarAdoptante($datos) {
    global $conn;
    
    $nombre = sanitizeInput($datos['nombre']);
    $email = sanitizeInput($datos['email']);
    $telefono = sanitizeInput($datos['telefono']);
    $direccion = sanitizeInput($datos['direccion']);
    $ocupacion = sanitizeInput($datos['ocupacion']);
    $experiencia = sanitizeInput($datos['experiencia']);
    $motivo = sanitizeInput($datos['motivo']);
    
    $sql = "INSERT INTO adoptantes (nombre, email, telefono, direccion, ocupacion, experiencia_mascotas, motivo_adopcion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $nombre, $email, $telefono, $direccion, $ocupacion, $experiencia, $motivo);
    
    return $stmt->execute();
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function obtenerEstadisticasAdopciones() {
    global $conn;
    
    $estadisticas = [
        'disponibles' => 0,
        'en_proceso' => 0,
        'adoptados' => 0,
        'solicitudes_pendientes' => 0
    ];
    
    // Contar animales por estado
    $result = $conn->query("
        SELECT estado, COUNT(*) as total 
        FROM animales 
        GROUP BY estado
    ");
    
    while ($row = $result->fetch_assoc()) {
        if ($row['estado'] == 'disponible') {
            $estadisticas['disponibles'] = $row['total'];
        } elseif ($row['estado'] == 'en_proceso') {
            $estadisticas['en_proceso'] = $row['total'];
        } elseif ($row['estado'] == 'adoptado') {
            $estadisticas['adoptados'] = $row['total'];
        }
    }
    
    // Contar solicitudes pendientes
    $result = $conn->query("
        SELECT COUNT(*) as total 
        FROM solicitudes_adopcion 
        WHERE estado = 'pendiente'
    ");
    
    $row = $result->fetch_assoc();
    $estadisticas['solicitudes_pendientes'] = $row['total'];
    
    return $estadisticas;
}

function listarAnimalesConSolicitudes() {
    global $conn;
    
    $animales = [];
    
    $result = $conn->query("
        SELECT a.*, 
               COUNT(s.id) AS solicitudes_pendientes
        FROM animales a
        LEFT JOIN solicitudes_adopcion s ON s.animal_id = a.id AND s.estado = 'pendiente'
        GROUP BY a.id
        ORDER BY a.estado, a.fecha_ingreso DESC
    ");
    
    while ($row = $result->fetch_assoc()) {
        $animales[] = $row;
    }
    
    return $animales;
}

function obtenerSolicitudesPendientes() {
    global $conn;
    
    $solicitudes = [];
    $sql = "SELECT s.*, a.nombre AS nombre_animal, u.nombre AS nombre_usuario
            FROM solicitudes_adopcion s
            JOIN animales a ON a.id = s.animal_id
            JOIN usuarios u ON u.id = s.usuario_id
            WHERE s.estado = 'pendiente'
            ORDER BY s.fecha_solicitud DESC";
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
    
    return $solicitudes;
}

function obtenerAnimalesEnProceso() {
    global $conn;
    
    $animales = [];
    $sql = "SELECT a.*, u.nombre AS nombre_adoptante, s.fecha_solicitud
            FROM animales a
            JOIN solicitudes_adopcion s ON s.animal_id = a.id
            JOIN usuarios u ON u.id = s.usuario_id
            WHERE a.estado = 'en_proceso' AND s.estado = 'aprobada'
            ORDER BY s.fecha_solicitud DESC";
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $animales[] = $row;
    }
    
    return $animales;
}

function listarAnimalesDisponibles() {
    global $conn;
    
    $animales = [];
    
    $result = $conn->query("
        SELECT * FROM animales 
        WHERE estado = 'disponible'
        ORDER BY fecha_ingreso DESC
    ");
    
    while ($row = $result->fetch_assoc()) {
        $animales[] = $row;
    }
    
    return $animales;
}

function mostrarMensajes() {
    if (isset($_SESSION['exito'])) {
        echo '<div class="alert alert-success">' . $_SESSION['exito'] . '</div>';
        unset($_SESSION['exito']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
}

function obtenerActividadReciente() {
    global $conn;
    
    $sql = "(
        SELECT 'adopcion' as tipo, 
               CONCAT('Solicitud de adopción para ', a.nombre) as descripcion,
               u.nombre as nombre_usuario,
               s.fecha_solicitud as fecha
        FROM solicitudes_adopcion s
        JOIN animales a ON s.animal_id = a.id
        JOIN usuarios u ON s.usuario_id = u.id
        ORDER BY s.fecha_solicitud DESC LIMIT 5
    ) UNION (
        SELECT 'donacion' as tipo,
               CONCAT('Donación de $', dm.monto) as descripcion,
               u.nombre as nombre_usuario,
               dm.fecha_donacion as fecha
        FROM donaciones_monetarias dm
        JOIN usuarios u ON dm.id_donante = u.id
        ORDER BY dm.fecha_donacion DESC LIMIT 5
    ) ORDER BY fecha DESC LIMIT 5";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obtenerAnimalPorId($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM animales WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function obtenerSolicitudesPorAnimal($animal_id) {
    global $conn;
    
    $sql = "SELECT s.*, a.nombre as nombre_animal, u.nombre as nombre_solicitante, u.email as email_solicitante
            FROM solicitudes_adopcion s
            JOIN animales a ON s.animal_id = a.id
            JOIN usuarios u ON s.usuario_id = u.id
            WHERE s.animal_id = ?
            ORDER BY s.fecha_solicitud DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $animal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obtenerSolicitudCompleta($solicitud_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT s.*, 
            s.motivo AS motivacion,
            s.experiencia AS experiencia_mascotas,
            a.nombre AS nombre_animal, 
            a.especie, 
            a.raza, 
            a.edad AS edad_animal, 
            a.estado AS estado_animal, 
            a.foto AS foto_animal,
            u.nombre AS nombre_solicitante, 
            u.email AS email_solicitante,
            u.telefono AS telefono_solicitante, 
            u.direccion AS direccion_solicitante,
            admin.nombre AS nombre_admin
        FROM solicitudes_adopcion s
        JOIN animales a ON a.id = s.animal_id
        JOIN usuarios u ON u.id = s.usuario_id
        LEFT JOIN usuarios admin ON admin.id = s.admin_id
        WHERE s.id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Error preparando la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $solicitud_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Error obteniendo resultados: " . $stmt->error);
    }
    
    return $result->fetch_assoc();
}

function obtenerSeguimientoAdopcion($solicitud_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.solicitud_id,
            s.tipo,
            s.descripcion,
            s.fecha,
            u.nombre AS nombre_responsable
        FROM seguimiento_adopciones s
        JOIN usuarios u ON u.id = s.responsable_id  /* Cambiado a responsable_id */
        WHERE s.solicitud_id = ?
        ORDER BY s.fecha DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Error preparando la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $solicitud_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Error obteniendo resultados: " . $stmt->error);
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obtenerSolicitudesAdopcion() {
    global $conn;
    
    $solicitudes = [];
    $sql = "SELECT s.*, 
                   a.nombre AS nombre_animal, 
                   u.nombre AS nombre_solicitante, 
                   u.email AS email_solicitante,
                   admin.nombre AS nombre_admin
            FROM solicitudes_adopcion s
            JOIN animales a ON a.id = s.animal_id
            JOIN usuarios u ON u.id = s.usuario_id
            LEFT JOIN usuarios admin ON admin.id = s.admin_id
            ORDER BY 
                CASE WHEN s.estado = 'pendiente' THEN 1
                     WHEN s.estado = 'aprobada' THEN 2
                     ELSE 3 END,
                s.fecha_solicitud DESC";
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
    
    return $solicitudes;
}
?>