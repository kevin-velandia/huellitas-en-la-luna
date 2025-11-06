<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/funciones.php';

// Limpieza de buffer y headers
if (ob_get_length()) ob_clean();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Validación de sesión
session_start();
if (!isset($_SESSION['usuario'])) {
    die(json_encode(['error' => 'Debes iniciar sesión']));
}

// Validación del ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die(json_encode(['error' => 'ID inválido']));
}

$solicitud_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario']['id'];

try {
    // Conexión a la base de datos usando tus constantes
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Obtener información básica de la solicitud
    $stmt = $conn->prepare("
        SELECT s.*, 
               a.nombre AS nombre_animal, 
               a.foto, 
               a.voluntario_id,
               u.nombre AS nombre_usuario,
               u.email AS email_usuario
        FROM solicitudes_adopcion s
        JOIN animales a ON a.id = s.animal_id
        JOIN usuarios u ON u.id = s.usuario_id
        WHERE s.id = ? AND (s.usuario_id = ? OR a.voluntario_id = ?)
    ");
    $stmt->bind_param("iii", $solicitud_id, $usuario_id, $usuario_id);
    $stmt->execute();
    $solicitud = $stmt->get_result()->fetch_assoc();

    if (!$solicitud) {
        die(json_encode(['error' => 'No tienes permiso para ver este seguimiento']));
    }

    // Determinar color del badge según el estado
    $badge_color = 'secondary';
    switch ($solicitud['estado']) {
        case 'aprobada': $badge_color = 'success'; break;
        case 'pendiente': $badge_color = 'warning'; break;
        case 'rechazada': $badge_color = 'danger'; break;
    }

    // Obtener historial de seguimiento
    $stmt = $conn->prepare("
        SELECT * FROM seguimiento_adopciones
        WHERE solicitud_id = ?
        ORDER BY fecha DESC
    ");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $seguimientos = $stmt->get_result();

    // Función para verificar y obtener ruta de imagen segura
    function getImagenSegura($foto) {
        $rutaBase = __DIR__ . '/uploads/adopciones/';
        $imagenDefault = 'default.jpg';
        
        // Verificar si la imagen solicitada existe
        if (!empty($foto) && file_exists($rutaBase . $foto) && is_file($rutaBase . $foto)) {
            return htmlspecialchars($foto);
        }
        
        // Verificar si la imagen por defecto existe
        if (file_exists($rutaBase . $imagenDefault)) {
            return $imagenDefault;
        }
        
        // Si no existe ninguna, devolver cadena vacía
        return '';
    }

    // Preparar datos para la vista usando SITE_URL
    $datos = [
        'solicitud' => [
            'id' => $solicitud_id,
            'nombre_animal' => htmlspecialchars($solicitud['nombre_animal']),
            'foto' => getImagenSegura($solicitud['foto']),
            'nombre_usuario' => htmlspecialchars($solicitud['nombre_usuario']),
            'email_usuario' => htmlspecialchars($solicitud['email_usuario']),
            'fecha_solicitud' => date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])),
            'estado' => htmlspecialchars($solicitud['estado']),
            'badge_color' => $badge_color,
            'motivo' => nl2br(htmlspecialchars($solicitud['motivo'])),
            'experiencia' => nl2br(htmlspecialchars($solicitud['experiencia'])),
            'hogar' => nl2br(htmlspecialchars($solicitud['hogar'])),
            'metodo_contacto' => ucfirst($solicitud['metodo_contacto'])
        ],
        'seguimientos' => [],
        'base_url' => SITE_URL // Usando tu constante definida
    ];

    while ($seguimiento = $seguimientos->fetch_assoc()) {
        $datos['seguimientos'][] = [
            'tipo' => ucfirst($seguimiento['tipo']),
            'fecha' => date('d/m/Y H:i', strtotime($seguimiento['fecha'])),
            'descripcion' => nl2br(htmlspecialchars($seguimiento['descripcion'])),
            'documento' => !empty($seguimiento['documento']) ? htmlspecialchars($seguimiento['documento']) : null
        ];
    }

    // Generar HTML de respuesta
    $html = <<<HTML
    <div class="container-fluid p-0">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Detalles de la Solicitud</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <img src="{$datos['base_url']}/uploads/adopciones/{$datos['solicitud']['foto']}"
                                     class="img-fluid rounded"
                                     style="max-height: 150px; object-fit: cover;"
                                     alt="{$datos['solicitud']['nombre_animal']}">
                            </div>
                            <div class="col-md-8">
                                <h5>{$datos['solicitud']['nombre_animal']}</h5>
                                <p><strong>Solicitante:</strong> {$datos['solicitud']['nombre_usuario']}</p>
                                <p><strong>Fecha:</strong> {$datos['solicitud']['fecha_solicitud']}</p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge bg-{$datos['solicitud']['badge_color']}">
                                        {$datos['solicitud']['estado']}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información del Solicitante</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Motivo:</strong> {$datos['solicitud']['motivo']}</p>
                        <p><strong>Experiencia:</strong> {$datos['solicitud']['experiencia']}</p>
                        <p><strong>Hogar:</strong> {$datos['solicitud']['hogar']}</p>
                        <p><strong>Contacto:</strong> {$datos['solicitud']['metodo_contacto']}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Historial de Seguimiento</h5>
            </div>
            <div class="card-body">
HTML;

    if (!empty($datos['seguimientos'])) {
        $html .= '<div class="timeline">';
        foreach ($datos['seguimientos'] as $seg) {
            $badgeColor = ($seg['tipo'] == 'Problema') ? 'danger' : (($seg['tipo'] == 'Finalizacion') ? 'success' : 'primary');
            
            $html .= <<<HTML
            <div class="timeline-item mb-3">
                <div class="timeline-badge bg-{$badgeColor}"></div>
                <div class="timeline-panel p-3 border rounded">
                    <div class="timeline-heading">
                        <h6 class="timeline-title fw-bold">
                            {$seg['tipo']}
                            <small class="text-muted float-end">{$seg['fecha']}</small>
                        </h6>
                    </div>
                    <div class="timeline-body">
                        <p>{$seg['descripcion']}</p>
HTML;
            
            if ($seg['documento']) {
                $html .= <<<HTML
                        <a href="{$datos['base_url']}/{$seg['documento']}" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-file-earmark"></i> Ver documento
                        </a>
HTML;
            }
            
            $html .= '</div></div></div>';
        }
        $html .= '</div>';
    } else {
        $html .= '<div class="alert alert-info mb-0">No hay registros de seguimiento para esta adopción.</div>';
    }

    $html .= <<<HTML
            </div>
        </div>
    </div>
HTML;

    echo $html;
    exit();

} catch (Exception $e) {
    die(json_encode(['error' => 'Error en el servidor: ' . htmlspecialchars($e->getMessage())]));
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}