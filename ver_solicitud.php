<?php
// ver_solicitud.php
require_once 'includes/header.php';
require_once 'includes/database.php';
require_once 'includes/funciones.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar que se haya proporcionado un ID de solicitud
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: mis_adopciones.php");
    exit();
}

$solicitud_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario']['id'];
$es_admin = $_SESSION['usuario']['rol'] === 'admin';

try {
    // Obtener información detallada de la solicitud
    $stmt = $conn->prepare("
        SELECT s.*, 
               u.nombre AS solicitante_nombre, 
               u.email AS solicitante_email,
               u.telefono AS solicitante_telefono,
               u.direccion AS solicitante_direccion,
               a.nombre AS animal_nombre,
               a.especie AS animal_especie,
               a.raza AS animal_raza,
               a.voluntario_id,
               v.nombre AS voluntario_nombre,
               v.email AS voluntario_email
        FROM solicitudes_adopcion s
        JOIN usuarios u ON u.id = s.usuario_id
        JOIN animales a ON a.id = s.animal_id
        JOIN usuarios v ON v.id = a.voluntario_id
        WHERE s.id = ? AND (a.voluntario_id = ? OR ?)
    ");
    $stmt->bind_param("iii", $solicitud_id, $usuario_id, $es_admin);
    $stmt->execute();
    $solicitud = $stmt->get_result()->fetch_assoc();

    if (!$solicitud) {
        throw new Exception("Solicitud no encontrada o no tienes permiso para verla");
    }

} catch (Exception $e) {
    die("<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<style>
     :root {
        --primary-color: #2e8b57;
        --secondary-color: #ff8c42;
        --dark-color: #333;
        --light-color: #f5f5dc;
        --success-color: #28a745;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        background-color: var(--light-color);
    }

    .btn {
        background: var(--primary-color);
        color: white;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        width: 100%;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 1.5rem;
    }
    
    .card-body {
        padding: 2rem;
        background-color: white;
    }
    
    .info-section {
        margin-bottom: 2rem;
    }
    
    .info-title {
        color: var(--primary-color);
        border-bottom: 2px solid var(--secondary-color);
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .info-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    
    .info-label {
        font-weight: 600;
        width: 200px;
        color: var(--dark-color);
    }
    
    .info-value {
        flex: 1;
    }
    
    .badge-estado {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #1e7a4a;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .info-row {
            flex-direction: column;
        }
        
        .info-label {
            width: 100%;
            margin-bottom: 0.3rem;
        }
    }
</style>

<div class="container my-5">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">Detalles de la solicitud de adopción</h2>
        </div>
        
        <div class="card-body">
            <!-- Estado de la solicitud -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="badge bg-<?= 
                    $solicitud['estado'] == 'aprobada' ? 'success' : 
                    ($solicitud['estado'] == 'pendiente' ? 'warning' : 'danger') 
                ?> badge-estado">
                    Estado: <?= ucfirst($solicitud['estado']) ?>
                </span>
                <small class="text-muted">
                    Fecha solicitud: <?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?>
                </small>
            </div>
            
            <!-- Información del solicitante -->
            <div class="info-section">
                <h3 class="info-title">Información del solicitante</h3>
                
                <div class="info-row">
                    <span class="info-label">Nombre completo:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud['solicitante_nombre']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Correo electrónico:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud['solicitante_email']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud['solicitante_telefono'] ?? 'No proporcionado') ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud['solicitante_direccion'] ?? 'No proporcionada') ?></span>
                </div>
                
                <?php if (isset($solicitud['motivacion'])): ?>
                <div class="info-row">
                    <span class="info-label">Motivación:</span>
                    <span class="info-value"><?= nl2br(htmlspecialchars($solicitud['motivacion'])) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($solicitud['experiencia_mascotas'])): ?>
                <div class="info-row">
                    <span class="info-label">Experiencia con mascotas:</span>
                    <span class="info-value"><?= nl2br(htmlspecialchars($solicitud['experiencia_mascotas'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Información del animal -->
            <div class="info-section">
                <h3 class="info-title">Información del animal</h3>
                
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud['animal_nombre']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Especie:</span>
                    <span class="info-value"><?= ucfirst($solicitud['animal_especie']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Raza:</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud['animal_raza']) ?></span>
                </div>
            </div>
            
            <!-- Información del voluntario responsable -->
            <?php if ($solicitud['voluntario_id'] == $usuario_id || $es_admin): ?>
                <div class="info-section">
                    <h3 class="info-title">Voluntario responsable</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value"><?= htmlspecialchars($solicitud['voluntario_nombre']) ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Correo electrónico:</span>
                        <span class="info-value"><?= htmlspecialchars($solicitud['voluntario_email']) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Notas adicionales -->
            <?php if (!empty($solicitud['notas'])): ?>
                <div class="info-section">
                    <h3 class="info-title">Notas adicionales</h3>
                    <p><?= nl2br(htmlspecialchars($solicitud['notas'])) ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Botones de acción -->
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                
                <?php if ($es_admin): ?>
                    <?php if ($solicitud['estado'] == 'pendiente'): ?>
                        <a href="aprobar_adopciones.php?id=<?= $solicitud['id'] ?>" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Aprobar solicitud
                        </a>
                        <a href="rechazar_adopciones.php?id=<?= $solicitud['id'] ?>" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Rechazar solicitud
                        </a>
                    <?php endif; ?>
                    
                    <a href="editar_solicitud.php?id=<?= $solicitud['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar solicitud
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php require_once 'includes/footer.php'; ?>