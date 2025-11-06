<?php
session_start();
require_once 'admin_header.php';
require_once '../includes/funciones.php';

// Verificar permisos de admin
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "No tienes permisos de administrador";
    header("Location: ../index.php");
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

// Obtener estadísticas actualizadas
$estadisticas = obtenerEstadisticasAdopciones();
$animales = listarAnimalesConSolicitudes();
?>

<div class="container">
    <h1 class="mb-4">Gestión de Adopciones</h1>
    
    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title"><i class="bi bi-heart"></i> Animales Disponibles</h5>
                            <p class="card-text display-4"><?= $estadisticas['disponibles'] ?></p>
                        </div>
                        <i class="bi bi-heart-fill display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title"><i class="bi bi-hourglass-split"></i> En Proceso</h5>
                            <p class="card-text display-4"><?= $estadisticas['en_proceso'] ?></p>
                        </div>
                        <i class="bi bi-hourglass display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title"><i class="bi bi-check-circle"></i> Adoptados</h5>
                            <p class="card-text display-4"><?= $estadisticas['adoptados'] ?></p>
                        </div>
                        <i class="bi bi-check-circle-fill display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pestañas -->
    <ul class="nav nav-tabs" id="adopcionesTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="animales-tab" data-bs-toggle="tab" data-bs-target="#animales" type="button">
                <i class="bi bi-hearts"></i> Animales
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="solicitudes-tab" data-bs-toggle="tab" data-bs-target="#solicitudes" type="button">
                <i class="bi bi-file-earmark-text"></i> Solicitudes
            </button>
        </li>
    </ul>
    
    <div class="tab-content border border-top-0 rounded-bottom p-3">
        
        <!-- Animales -->
        <div class="tab-pane fade show active" id="animales">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Edad</th>
                            <th>Estado</th>
                            <th>Solicitudes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animales as $animal): ?>
                        <tr>
                            <td><?= htmlspecialchars($animal['nombre']) ?></td>
                            <td><?= htmlspecialchars($animal['especie']) ?></td>
                            <td><?= htmlspecialchars($animal['raza']) ?></td>
                            <td><?= $animal['edad'] ?> años</td>
                            <td>
                                <span class="badge bg-<?= 
                                    $animal['estado'] == 'disponible' ? 'success' : 
                                    ($animal['estado'] == 'en_proceso' ? 'warning' : 'primary') 
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $animal['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($animal['solicitudes_pendientes'] > 0): ?>
                                    <a href="gestion_solicitudes.php?animal_id=<?= $animal['id'] ?>" 
                                       class="btn btn-sm btn-info position-relative">
                                        <?= $animal['solicitudes_pendientes'] ?>
                                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                            <span class="visually-hidden">Solicitudes pendientes</span>
                                        </span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="editar_animal.php?id=<?= $animal['id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <a href="ver_animal.php?id=<?= $animal['id'] ?>" 
                                       class="btn btn-sm btn-secondary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Solicitudes -->
        <div class="tab-pane fade" id="solicitudes">
            <?php 
            $solicitudes = obtenerSolicitudesAdopcion();
            if (!empty($solicitudes)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Animal</th>
                                <th>Solicitante</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?= htmlspecialchars($solicitud['nombre_animal']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($solicitud['nombre_solicitante']) ?></td>
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
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <?php if ($solicitud['estado'] == 'pendiente'): ?>
                                            <a href="aprobar_adopciones.php?id=<?= $solicitud['id'] ?>" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check-circle"></i> Aprobar
                                            </a>
                                            <a href="rechazar_adopciones.php?id=<?= $solicitud['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle"></i> Rechazar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No hay solicitudes de adopción pendientes.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>