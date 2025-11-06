<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';
require_once 'admin_header.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] != 'admin') {
    redirect('../login.php');
}

$sql_monetarias = "SELECT d.*, u.nombre as nombre_donante 
                   FROM donaciones_monetarias d
                   LEFT JOIN usuarios u ON d.id_donante = u.id
                   ORDER BY d.fecha_donacion DESC";
$monetarias = $conn->query($sql_monetarias)->fetch_all(MYSQLI_ASSOC);

$sql_especie = "SELECT d.*, u.nombre as nombre_donante 
                FROM donaciones_especie d
                LEFT JOIN usuarios u ON d.id_donante = u.id
                ORDER BY d.fecha_donacion DESC";
$especie = $conn->query($sql_especie)->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h1 class="h4 mb-0">Gestión de Donaciones</h1>
        </div>
        
        <div class="card-body">
            <ul class="nav nav-tabs" id="donacionesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="monetarias-tab" data-bs-toggle="tab" data-bs-target="#monetarias" type="button" role="tab">
                        <i class="bi bi-cash-coin"></i> Monetarias
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="especie-tab" data-bs-toggle="tab" data-bs-target="#especie" type="button" role="tab">
                        <i class="bi bi-box-seam"></i> En Especie
                    </button>
                </li>
            </ul>
            
            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="donacionesTabContent">
                <div class="tab-pane fade show active" id="monetarias" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Donante</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monetarias as $donacion): ?>
                                <tr>
                                    <td><?= (int)$donacion['id'] ?></td>
                                    <td><?= htmlspecialchars($donacion['nombre_donante'] ?? 'Anónimo') ?></td>
                                    <td class="fw-bold">$<?= number_format($donacion['monto'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= ucfirst(htmlspecialchars($donacion['metodo_pago'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($donacion['fecha_donacion'])) ?></td>
                                    <td>
                                        <a href="detalle_donacion.php?tipo=<?= urlencode('monetaria') ?>&id=<?= (int)$donacion['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="especie" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Donante</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($especie as $donacion): ?>
                                <tr>
                                    <td><?= (int)$donacion['id'] ?></td>
                                    <td><?= htmlspecialchars($donacion['nombre_donante'] ?? 'Anónimo') ?></td>
                                    <td><?= ucfirst(htmlspecialchars($donacion['tipo'])) ?></td>
                                    <td>
                                        <span data-bs-toggle="tooltip" data-bs-title="<?= htmlspecialchars($donacion['descripcion']) ?>">
                                            <?= substr(htmlspecialchars($donacion['descripcion']), 0, 30) ?>...
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($donacion['cantidad']) . ' ' . htmlspecialchars($donacion['unidad']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($donacion['fecha_donacion'])) ?></td>
                                    <td>
                                        <?php 
                                        $estadoClass = [
                                            'recibido' => 'success',
                                            'pendiente' => 'warning',
                                            'rechazado' => 'danger',
                                            'procesado' => 'primary'
                                        ][$donacion['estado']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $estadoClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($donacion['estado']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="detalle_donacion.php?tipo=<?= urlencode('especie') ?>&id=<?= (int)$donacion['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="editar_donacion.php?tipo=<?= urlencode('especie') ?>&id=<?= (int)$donacion['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar donación">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
