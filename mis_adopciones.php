<?php
require_once 'includes/header.php';
require_once 'includes/database.php';
require_once 'includes/funciones.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];

try {
    // Animales registrados por el voluntario
    $stmt_animales = $conn->prepare("
        SELECT a.*, 
               COUNT(s.id) AS solicitudes_pendientes
        FROM animales a
        LEFT JOIN solicitudes_adopcion s ON s.animal_id = a.id AND s.estado = 'pendiente'
        WHERE a.voluntario_id = ?
        GROUP BY a.id
        ORDER BY a.estado, a.fecha_ingreso DESC
    ");
    $stmt_animales->bind_param("i", $id_usuario);
    $stmt_animales->execute();
    $animales = $stmt_animales->get_result();

    // Solicitudes de adopción realizadas por el voluntario
    $stmt_solicitudes = $conn->prepare("
        SELECT s.*, a.nombre AS nombre_animal, a.foto
        FROM solicitudes_adopcion s
        JOIN animales a ON a.id = s.animal_id
        WHERE s.usuario_id = ?
        ORDER BY s.fecha_solicitud DESC
    ");
    $stmt_solicitudes->bind_param("i", $id_usuario);
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
    
    .badge-estado {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
    
    .tab-content {
        background-color: white;
        border-radius: 0 0 10px 10px;
        padding: 1.5rem;
    }
    
    .nav-tabs .nav-link.active {
        font-weight: bold;
        background-color: white;
        border-bottom-color: white;
    }

    /* Eliminar backdrop completamente */
.modal-backdrop {
    display: none !important;
}

/* Opcional: Fondo semitransparente personalizado */
body.modal-open {
    overflow: auto !important;
    padding-right: 0 !important;
}
</style>

<div class="container my-5">
    <h1 class="mb-4">Mis Adopciones</h1>
    
    <ul class="nav nav-tabs" id="adopcionesTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="animales-tab" data-bs-toggle="tab" data-bs-target="#animales">
                <i class="bi bi-hearts"></i> Mis Animales
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="solicitudes-tab" data-bs-toggle="tab" data-bs-target="#solicitudes">
                <i class="bi bi-heart"></i> Mis Solicitudes
            </button>
        </li>
    </ul>
    
    <div class="tab-content border border-top-0 rounded-bottom">
        <div class="tab-pane fade show active" id="animales">
            <?php if ($animales->num_rows > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php while($animal = $animales->fetch_assoc()): ?>
                        <div class="col">
                            <div class="animal-card h-100">
                                <img src="uploads/adopciones/<?= htmlspecialchars($animal['foto']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($animal['nombre']) ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($animal['nombre']) ?></h5>
                                    <p class="card-text">
                                        <strong>Especie:</strong> <?= ucfirst($animal['especie']) ?><br>
                                        <strong>Edad:</strong> <?= $animal['edad'] ?> años<br>
                                        <strong>Raza:</strong> <?= htmlspecialchars($animal['raza']) ?>
                                    </p>
                                    <span class="badge bg-<?= 
                                        $animal['estado'] == 'disponible' ? 'success' : 
                                        ($animal['estado'] == 'en_proceso' ? 'warning' : 'primary') 
                                    ?> badge-estado">
                                        <?= ucfirst(str_replace('_', ' ', $animal['estado'])) ?>
                                    </span>
                                    
                                    <?php if ($animal['solicitudes_pendientes'] > 0): ?>
                                        <span class="badge bg-info badge-estado">
                                            <?= $animal['solicitudes_pendientes'] ?> solicitud(es)
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="detalle_adopcion.php?id=<?= $animal['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No has registrado animales para adopción aún. 
                    <a href="formulario_dar_adopcion.php" class="alert-link">Registrar un animal</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tab-pane fade" id="solicitudes">
            <?php if ($solicitudes->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Animal</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($solicitud = $solicitudes->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="uploads/adopciones/<?= htmlspecialchars($solicitud['foto']) ?>" 
                                             class="rounded-circle me-3" 
                                             width="50" 
                                             height="50" 
                                             style="object-fit: cover;">
                                        <?= htmlspecialchars($solicitud['nombre_animal']) ?>
                                    </div>
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
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="verSeguimiento(<?= $solicitud['id'] ?>)">
                                        <i class="bi bi-eye"></i> Ver seguimiento
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No has realizado solicitudes de adopción. 
                    <a href="adopciones.php" class="alert-link">Ver animales disponibles</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para seguimiento - Versión corregida -->
<div class="modal fade" id="seguimientoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Seguimiento de Adopción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalSeguimientoContent">
                <!-- Contenido se cargará por AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>

function verSeguimiento(solicitudId) {
    // Eliminar backdrops existentes primero
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.paddingRight = '';
    document.body.style.overflow = '';

    // Mostrar spinner
    document.getElementById('modalSeguimientoContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando seguimiento...</p>
        </div>`;
    
    // Inicializar modal correctamente
    var modalEl = document.getElementById('seguimientoModal');
    var modal = new bootstrap.Modal(modalEl);
    
    // Evento para limpiar al cerrar
    modalEl.addEventListener('hidden.bs.modal', function() {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    });
    
    modal.show();
    
    // Cargar contenido
    fetch('obtener_seguimiento.php?id=' + solicitudId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalSeguimientoContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('modalSeguimientoContent').innerHTML = `
                <div class="alert alert-danger">
                    Error al cargar: ${error.message}
                </div>`;
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>