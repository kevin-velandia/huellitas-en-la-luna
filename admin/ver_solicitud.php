<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

session_start();

// Verificar permisos de admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Verificar que se recibe un ID válido
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Solicitud no especificada.";
    header("Location: gestion_adopciones.php");
    exit();
}

$solicitud_id = (int)$_GET['id'];

// Obtener información completa de la solicitud
$solicitud = obtenerSolicitudCompleta($solicitud_id);
if (!$solicitud) {
    $_SESSION['error'] = "Solicitud no encontrada.";
    header("Location: gestion_adopciones.php");
    exit();
}

// Obtener historial de seguimiento
$seguimiento = obtenerSeguimientoAdopcion($solicitud_id);

include 'admin_header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Solicitud #<?= $solicitud['id'] ?></h1>
        <a href="gestion_solicitudes.php<?= isset($_GET['animal_id']) ? '?animal_id='.(int)$_GET['animal_id'] : '' ?>" 
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <!-- Información del animal -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información del Animal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if ($solicitud['foto_animal']): ?>
                                <img src="../uploads/adopciones/<?= htmlspecialchars($solicitud['foto_animal']) ?>" 
                                     alt="<?= htmlspecialchars($solicitud['nombre_animal']) ?>" 
                                     class="img-fluid rounded">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5><?= htmlspecialchars($solicitud['nombre_animal']) ?></h5>
                            <p><strong>Especie:</strong> <?= ucfirst(htmlspecialchars($solicitud['especie'])) ?></p>
                            <p><strong>Raza:</strong> <?= htmlspecialchars($solicitud['raza']) ?></p>
                            <p><strong>Edad:</strong> <?= htmlspecialchars($solicitud['edad_animal']) ?> años</p>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-<?= 
                                    $solicitud['estado_animal'] == 'disponible' ? 'success' : 
                                    ($solicitud['estado_animal'] == 'en_proceso' ? 'warning' : 'primary') 
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $solicitud['estado_animal'])) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del solicitante -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Información del Solicitante</h5>
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($solicitud['nombre_solicitante']) ?></h5>
                    <p><strong>Email:</strong> <?= htmlspecialchars($solicitud['email_solicitante']) ?></p>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($solicitud['telefono_solicitante'] ?? 'No especificado') ?></p>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($solicitud['direccion_solicitante'] ?? 'No especificada') ?></p>
                    <p><strong>Fecha solicitud:</strong> <?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles de la solicitud -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Detalles de la Solicitud</h5>
        </div>
        <div class="card-body">
            <?php if (isset($solicitud['motivacion']) && !empty($solicitud['motivacion'])): ?>
            <div class="mb-3">
                <label class="form-label"><strong>Motivación:</strong></label>
                <p><?= nl2br(htmlspecialchars($solicitud['motivacion'])) ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($solicitud['experiencia']) && !empty($solicitud['experiencia'])): ?>
            <div class="mb-3">
                <label class="form-label"><strong>Experiencia previa:</strong></label>
                <p><?= nl2br(htmlspecialchars($solicitud['experiencia'])) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label"><strong>Estado:</strong></label>
                <span class="badge bg-<?= 
                    $solicitud['estado'] == 'aprobada' ? 'success' : 
                    ($solicitud['estado'] == 'pendiente' ? 'warning' : 'danger') 
                ?>">
                    <?= ucfirst($solicitud['estado']) ?>
                </span>
                <?php if (isset($solicitud['fecha_resolucion']) && !empty($solicitud['fecha_resolucion'])): ?>
                    <span class="text-muted ms-2">(Resuelta el <?= date('d/m/Y', strtotime($solicitud['fecha_resolucion'])) ?>)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Historial de seguimiento -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Seguimiento</h5>
                <?php if ($solicitud['estado'] == 'aprobada'): ?>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoSeguimientoModal">
                        <i class="bi bi-plus"></i> Agregar seguimiento
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($seguimiento)): ?>
                <div class="timeline">
                    <?php foreach ($seguimiento as $item): ?>
                        <div class="timeline-item">
                            <div class="timeline-item-marker">
                                <div class="timeline-item-marker-indicator bg-<?= 
                                    $item['tipo'] == 'seguimiento' ? 'info' : 'success' 
                                ?>"></div>
                            </div>
                            <div class="timeline-item-content">
                                <div class="d-flex justify-content-between">
                                    <h6><?= ucfirst($item['tipo']) ?></h6>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($item['fecha'])) ?></small>
                                </div>
                                <p><?= nl2br(htmlspecialchars($item['descripcion'])) ?></p>
                                <small class="text-muted">Responsable: <?= htmlspecialchars($item['nombre_responsable']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No hay registros de seguimiento para esta solicitud.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Acciones -->
    <div class="card">
        <div class="card-body text-center">
            <div class="d-flex justify-content-center gap-3">
                <?php if ($solicitud['estado'] == 'pendiente'): ?>
                    <a href="aprobar_adopciones.php?id=<?= $solicitud['id'] ?>" 
                       class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Aprobar
                    </a>
                    <a href="rechazar_adopciones.php?id=<?= $solicitud['id'] ?>" 
                       class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Rechazar
                    </a>
                <?php elseif ($solicitud['estado'] == 'aprobada'): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#completarModal">
                        <i class="bi bi-check-all"></i> Marcar como completada
                    </button>
                <?php endif; ?>
                <a href="gestion_solicitudes.php<?= isset($_GET['animal_id']) ? '?animal_id='.(int)$_GET['animal_id'] : '' ?>" 
                   class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nuevo seguimiento -->
<div class="modal fade" id="nuevoSeguimientoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Seguimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesar_seguimiento.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="solicitud_id" value="<?= $solicitud['id'] ?>">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="seguimiento">Seguimiento</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal específico para completar adopción -->
<div class="modal fade" id="completarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Completar Adopción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="completar_adopcion.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="solicitud_id" value="<?= $solicitud['id'] ?>">
                    <input type="hidden" name="tipo" value="completado">
                    <div class="mb-3">
                        <label for="descripcion_completado" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control" id="descripcion_completado" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Completado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>