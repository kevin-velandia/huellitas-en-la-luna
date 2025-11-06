<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

// Verificar permisos de admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Mostrar mensajes de éxito/error
if (isset($_SESSION['exito'])) {
    echo '<div class="alert alert-success">'.$_SESSION['exito'].'</div>';
    unset($_SESSION['exito']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}

// Obtener ID del animal (si se especifica)
$animal_id = isset($_GET['animal_id']) ? (int)$_GET['animal_id'] : 0;

// Obtener solicitudes
if ($animal_id > 0) {
    $solicitudes = obtenerSolicitudesPorAnimal($animal_id);
    $animal = obtenerAnimalPorId($animal_id);
} else {
    $solicitudes = obtenerSolicitudesAdopcion();
    $animal = null;
}

include 'admin_header.php';
?>

<div class="container mt-4">
    <?php if ($animal): ?>
        <h1 class="mb-4">Solicitudes para <?= htmlspecialchars($animal['nombre']) ?></h1>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <?php if ($animal['foto']): ?>
                            <img src="../uploads/adopciones/<?= htmlspecialchars($animal['foto']) ?>" 
                                 alt="<?= htmlspecialchars($animal['nombre']) ?>" 
                                 class="img-fluid rounded">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9">
                        <h5>Información del animal</h5>
                        <p><strong>Especie:</strong> <?= ucfirst(htmlspecialchars($animal['especie'])) ?></p>
                        <p><strong>Raza:</strong> <?= htmlspecialchars($animal['raza']) ?></p>
                        <p><strong>Edad:</strong> <?= htmlspecialchars($animal['edad']) ?> años</p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?= 
                                $animal['estado'] == 'disponible' ? 'success' : 
                                ($animal['estado'] == 'en_proceso' ? 'warning' : 'primary') 
                            ?>">
                                <?= ucfirst(str_replace('_', ' ', $animal['estado'])) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <h1 class="mb-4">Todas las solicitudes de adopción</h1>
    <?php endif; ?>
    
    <?php if (!empty($solicitudes)): ?>
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th># Solicitud</th>
                                <?php if (!$animal): ?>
                                    <th>Animal</th>
                                <?php endif; ?>
                                <th>Solicitante</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td><?= $solicitud['id'] ?></td>
                                <?php if (!$animal): ?>
                                    <td>
                                        <a href="gestion_solicitudes.php?animal_id=<?= $solicitud['animal_id'] ?>">
                                            <?= htmlspecialchars($solicitud['nombre_animal']) ?>
                                        </a>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?= htmlspecialchars($solicitud['nombre_solicitante']) ?>
                                    <small class="text-muted d-block"><?= htmlspecialchars($solicitud['email_solicitante']) ?></small>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $solicitud['estado'] == 'aprobada' ? 'success' : 
                                        ($solicitud['estado'] == 'pendiente' ? 'warning' : 'danger') 
                                    ?>">
                                        <?= ucfirst($solicitud['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="ver_solicitud.php?id=<?= $solicitud['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        <?php if ($solicitud['estado'] == 'pendiente'): ?>
                                            <a href="aprobar_adopciones.php?id=<?= $solicitud['id'] ?>" 
                                               class="btn btn-sm btn-outline-success" title="Aprobar">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                            <a href="rechazar_adopciones.php?id=<?= $solicitud['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" title="Rechazar">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($solicitud['estado'] == 'aprobada'): ?>
                                            <a href="completar_adopcion.php?id=<?= $solicitud['id'] ?>" 
                                               class="btn btn-sm btn-outline-info" title="Marcar como completada">
                                                <i class="bi bi-check-all"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No hay solicitudes de adopción <?= $animal ? 'para este animal' : 'pendientes' ?>.
        </div>
    <?php endif; ?>
    
    <?php if ($animal): ?>
        <div class="mt-3">
            <a href="gestion_adopciones.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a la gestión de adopciones
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'admin_footer.php'; ?>