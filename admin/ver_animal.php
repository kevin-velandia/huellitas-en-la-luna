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

// Verificar que se haya proporcionado un ID de animal
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de animal no válido";
    header("Location: gestion_adopciones.php");
    exit();
}

$animal_id = intval($_GET['id']);

// Obtener información del animal
$animal = obtenerAnimalPorId($animal_id);
if (!$animal) {
    $_SESSION['error'] = "Animal no encontrado";
    header("Location: gestion_adopciones.php");
    exit();
}

// Obtener solicitudes relacionadas con este animal
$solicitudes = obtenerSolicitudesPorAnimal($animal_id);

// Mostrar mensajes de éxito/error
if (isset($_SESSION['exito'])) {
    echo '<div class="alert alert-success">'.$_SESSION['exito'].'</div>';
    unset($_SESSION['exito']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Detalles del Animal</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="gestion_adopciones.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php if ($animal['foto']): ?>
                        <img src="../uploads/adopciones/<?= htmlspecialchars($animal['foto']) ?>" 
                             class="img-fluid rounded mb-3" alt="<?= htmlspecialchars($animal['nombre']) ?>">
                    <?php else: ?>
                        <div class="bg-light p-5 mb-3 rounded">
                            <i class="bi bi-image display-4 text-muted"></i>
                            <p class="mt-2">Sin imagen</p>
                        </div>
                    <?php endif; ?>
                    
                    <h3><?= htmlspecialchars($animal['nombre']) ?></h3>
                    <span class="badge bg-<?= 
                        $animal['estado'] == 'disponible' ? 'success' : 
                        ($animal['estado'] == 'en_proceso' ? 'warning' : 'primary') 
                    ?>">
                        <?= ucfirst(str_replace('_', ' ', $animal['estado'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="editar_animal.php?id=<?= $animal['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información Básica</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Especie:</strong> <?= ucfirst(htmlspecialchars($animal['especie'])) ?></p>
                            <p><strong>Raza:</strong> <?= htmlspecialchars($animal['raza']) ? htmlspecialchars($animal['raza']) : 'No especificada' ?></p>
                            <p><strong>Edad:</strong> <?= $animal['edad'] ?> años</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tamaño:</strong> <?= $animal['tamano'] ? ucfirst(htmlspecialchars($animal['tamano'])) : 'No especificado' ?></p>
                            <p><strong>Género:</strong> <?= $animal['genero'] ? ucfirst(htmlspecialchars($animal['genero'])) : 'No especificado' ?></p>
                            <p><strong>Fecha de ingreso:</strong> <?= date('d/m/Y', strtotime($animal['fecha_ingreso'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Salud</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Vacunado:</strong> 
                                <span class="badge bg-<?= $animal['vacunado'] == 'si' ? 'success' : 'danger' ?>">
                                    <?= ucfirst(htmlspecialchars($animal['vacunado'])) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Esterilizado:</strong> 
                                <span class="badge bg-<?= $animal['esterilizado'] == 'si' ? 'success' : 'danger' ?>">
                                    <?= ucfirst(htmlspecialchars($animal['esterilizado'])) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <p><strong>Comportamiento:</strong> <?= htmlspecialchars($animal['comportamiento']) ? htmlspecialchars($animal['comportamiento']) : 'No especificado' ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Descripción</h5>
                </div>
                <div class="card-body">
                    <p><?= htmlspecialchars($animal['descripcion']) ? nl2br(htmlspecialchars($animal['descripcion'])) : 'No hay descripción disponible' ?></p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Solicitudes de Adopción</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($solicitudes)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Solicitante</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitudes as $solicitud): ?>
                                    <tr>
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
                            No hay solicitudes de adopción para este animal.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>