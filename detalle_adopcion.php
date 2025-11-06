<?php
require_once 'includes/header.php';
require_once 'includes/database.php';
require_once 'includes/funciones.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar que se haya proporcionado un ID de animal
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: mis_adopciones.php");
    exit();
}

$animal_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario']['id'];

try {
    // Obtener información del animal
    $stmt = $conn->prepare("
        SELECT a.*, u.nombre AS voluntario_nombre, u.email AS voluntario_email
        FROM animales a
        JOIN usuarios u ON u.id = a.voluntario_id
        WHERE a.id = ? AND (a.voluntario_id = ? OR ?)
    ");
    $es_admin = $_SESSION['usuario']['rol'] === 'admin' ? 1 : 0;
    $stmt->bind_param("iii", $animal_id, $usuario_id, $es_admin);
    $stmt->execute();
    $animal = $stmt->get_result()->fetch_assoc();

    if (!$animal) {
        throw new Exception("Animal no encontrado o no tienes permiso para verlo");
    }

    // Obtener solicitudes de adopción para este animal
    $stmt_solicitudes = $conn->prepare("
        SELECT s.*, u.nombre AS solicitante_nombre, u.email AS solicitante_email
        FROM solicitudes_adopcion s
        JOIN usuarios u ON u.id = s.usuario_id
        WHERE s.animal_id = ?
        ORDER BY s.fecha_solicitud DESC
    ");
    $stmt_solicitudes->bind_param("i", $animal_id);
    $stmt_solicitudes->execute();
    $solicitudes = $stmt_solicitudes->get_result();

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
    
    .animal-header {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .animal-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        max-height: 1000px;
    }
    
    .animal-info {
        padding: 2rem;
    }
    
    .animal-title {
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    
    .badge-estado {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    
    .solicitudes-section {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-top: 2rem;
    }
    
    .solicitud-card {
        border-left: 4px solid var(--secondary-color);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background-color: #f9f9f9;
        border-radius: 0 5px 5px 0;
        transition: all 0.3s ease;
    }
    
    .solicitud-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .contact-info {
        background-color: #f0f8ff;
        padding: 1rem;
        border-radius: 5px;
        margin-top: 1rem;
    }
    
    .actions-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .animal-img {
            height: 300px;
        }
        
        .actions-container {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            margin: 0.2rem 0;
        }
    }
</style>

<div class="container my-5">
    <div class="animal-header">
        <div class="row">
            <div class="col-md-6">
                <img src="uploads/adopciones/<?= htmlspecialchars($animal['foto']) ?>" 
                     class="animal-img" 
                     alt="<?= htmlspecialchars($animal['nombre']) ?>">
            </div>
            <div class="col-md-6">
                <div class="animal-info">
                    <h1 class="animal-title"><?= htmlspecialchars($animal['nombre']) ?></h1>
                    
                    <span class="badge bg-<?= 
                        $animal['estado'] == 'disponible' ? 'success' : 
                        ($animal['estado'] == 'en_proceso' ? 'warning' : 'primary') 
                    ?> badge-estado mb-3">
                        <?= ucfirst(str_replace('_', ' ', $animal['estado'])) ?>
                    </span>
                    
                    <p><strong>Especie:</strong> <?= ucfirst($animal['especie']) ?></p>
                    <p><strong>Edad:</strong> <?= $animal['edad'] ?> años</p>
                    <p><strong>Raza:</strong> <?= htmlspecialchars($animal['raza']) ?></p>
                    <p><strong>Tamaño:</strong> <?= ucfirst($animal['tamano']) ?></p>
                    <p><strong>Género:</strong> <?= ucfirst($animal['genero']) ?></p>
                    <p><strong>Fecha de ingreso:</strong> <?= date('d/m/Y', strtotime($animal['fecha_ingreso'])) ?></p>
                    
                    <h4 class="mt-4">Descripción</h4>
                    <p><?= nl2br(htmlspecialchars($animal['descripcion'])) ?></p>
                    
                    <?php if ($animal['voluntario_id'] == $usuario_id || $_SESSION['usuario']['rol'] === 'admin'): ?>
                        <div class="contact-info mt-4">
                            <h5>Información del responsable</h5>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($animal['voluntario_nombre']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($animal['voluntario_email']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($animal['voluntario_id'] == $usuario_id || $_SESSION['usuario']['rol'] === 'admin'): ?>
        <div class="solicitudes-section">
            <h2>Solicitudes de adopción</h2>
            
            <?php if ($solicitudes->num_rows > 0): ?>
                <?php while($solicitud = $solicitudes->fetch_assoc()): ?>
                    <div class="solicitud-card">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div>
                                <h5><?= htmlspecialchars($solicitud['solicitante_nombre']) ?></h5>
                                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge bg-<?= 
                                        $solicitud['estado'] == 'aprobada' ? 'success' : 
                                        ($solicitud['estado'] == 'pendiente' ? 'warning' : 'danger') 
                                    ?>">
                                        <?= ucfirst($solicitud['estado']) ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="actions-container mt-2 mt-md-0">
                                <a href="ver_solicitud.php?id=<?= $solicitud['id'] ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-eye"></i> Ver detalles
                                </a>
                            </div>
                        </div>
                        
                        <div class="contact-info mt-2">
                            <p><strong>Contacto:</strong> <?= htmlspecialchars($solicitud['solicitante_email']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    No hay solicitudes de adopción para este animal.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="d-flex flex-wrap gap-2 mt-4">
        <a href="mis_adopciones.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        
        <?php if ($animal['voluntario_id'] == $usuario_id || $_SESSION['usuario']['rol'] === 'admin'): ?>
            <a href="editar_animal.php?id=<?= $animal['id'] ?>" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Editar información
            </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>