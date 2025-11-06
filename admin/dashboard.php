<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

if (!isset($_SESSION['usuario'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error'] = "Por favor inicia sesión";
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "No tienes permisos de administrador";
    header("Location: ../index.php");
    exit();
}

$stats = [
    'animales_disponibles' => 0,
    'en_proceso' => 0,
    'adoptados' => 0,
    'adopciones_pendientes' => 0,
    'donaciones_mes' => 0
];

try {
    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM animales WHERE estado = 'disponible') as animales_disponibles,
            (SELECT COUNT(*) FROM animales WHERE estado = 'en_proceso') as en_proceso,
            (SELECT COUNT(*) FROM animales WHERE estado = 'adoptado') as adoptados,
            (SELECT COUNT(*) FROM solicitudes_adopcion WHERE estado = 'pendiente') as adopciones_pendientes,
            (SELECT COALESCE(SUM(monto), 0) FROM donaciones_monetarias 
             WHERE MONTH(fecha_donacion) = MONTH(CURRENT_DATE()) 
             AND YEAR(fecha_donacion) = YEAR(CURRENT_DATE())) as donaciones_mes
    ";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $stats = $result->fetch_assoc();
    }
    
    // Obtener actividad reciente
    $actividad_reciente = obtenerActividadReciente();
    
} catch (Exception $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    $_SESSION['error'] = "Error al cargar estadísticas. Por favor intente más tarde.";
}

include 'admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Bienvenido Administrador: <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h1>
        
                
                <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title"><i class="bi bi-heart"></i> Animales Disponibles</h5>
                                    <p class="card-text display-4"><?= $stats['animales_disponibles'] ?></p>
                                </div>
                                <i class="bi bi-heart-fill display-4 opacity-50"></i>
                            </div>
                            <a href="gestion_adopciones.php" class="btn btn-outline-light btn-sm mt-2">
                                Ver todos <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title"><i class="bi bi-clock"></i> Adopciones Pendientes</h5>
                                    <p class="card-text display-4"><?= $stats['adopciones_pendientes'] ?></p>
                                </div>
                                <i class="bi bi-clock-fill display-4 opacity-50"></i>
                            </div>
                            <a href="gestion_adopciones.php" class="btn btn-outline-light btn-sm mt-2">
                                Gestionar <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title"><i class="bi bi-cash"></i> Donaciones este mes</h5>
                                    <p class="card-text display-4">$<?= number_format($stats['donaciones_mes'], 2) ?></p>
                                </div>
                                <i class="bi bi-cash-stack display-4 opacity-50"></i>
                            </div>
                            <a href="gestion_donaciones.php" class="btn btn-outline-light btn-sm mt-2">
                                Ver detalle <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge"></i> Acciones Rápidas</h5>
                    <span class="badge bg-light text-primary">Admin</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="gestion_donaciones.php" class="btn btn-success w-100 py-3">
                                <i class="bi bi-heart-fill fs-4"></i><br>
                                Donaciones
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="gestion_adopciones.php" class="btn btn-warning w-100 py-3">
                                <i class="bi bi-house-heart fs-4"></i><br>
                                Adopciones
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="gestion_inventario.php" class="btn btn-secondary w-100 py-3">
                                <i class="bi bi-box-seam fs-4"></i><br>
                                Inventario
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="gestion_usuarios.php" class="btn btn-dark w-100 py-3">
                                <i class="bi bi-people-fill fs-4"></i><br>
                                Usuarios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4 shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-activity"></i> Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($actividad_reciente)): ?>
                                    <?php foreach ($actividad_reciente as $actividad): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $actividad['tipo'] == 'adopcion' ? 'primary' : 
                                                    ($actividad['tipo'] == 'donacion' ? 'success' : 'secondary')
                                                ?>">
                                                    <?= ucfirst($actividad['tipo']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($actividad['descripcion']) ?></td>
                                            <td><?= htmlspecialchars($actividad['nombre_usuario']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($actividad['fecha'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay actividad reciente</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>