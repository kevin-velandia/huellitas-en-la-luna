<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

// Verificación de sesión y rol
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Consulta a la base de datos
$sql = "SELECT i.*, 
               CASE 
                   WHEN i.tipo = 'comida' THEN 'Comida para animales'
                   WHEN i.tipo = 'medicamento' THEN 'Medicamentos'
                   WHEN i.tipo = 'accesorio' THEN 'Accesorios'
                   ELSE 'Otros'
               END as tipo_descripcion
        FROM inventario i
        ORDER BY i.fecha_ingreso DESC";
$result = $conn->query($sql);

// Incluir cabecera
require_once 'admin_header.php';
?>

<div class="d-flex justify-content-between mb-4">
    <h2 class="mb-0">Gestión de Inventario</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoItemModal">
        <i class="bi bi-plus-circle"></i> Nuevo Item
    </button>
</div>

<?php mostrarMensajes(); ?>

<div class="card inventory-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Fecha Ingreso</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($item = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td><?= $item['tipo_descripcion'] ?></td>
                            <td><?= $item['cantidad'] ?></td>
                            <td><?= $item['unidad'] ?></td>
                            <td><?= date('d/m/Y', strtotime($item['fecha_ingreso'])) ?></td>
                            <td><?= $item['fecha_vencimiento'] ? date('d/m/Y', strtotime($item['fecha_vencimiento'])) : 'N/A' ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $item['estado'] == 'disponible' ? 'success' : 
                                    ($item['estado'] == 'bajo' ? 'warning' : 
                                    ($item['estado'] == 'agotado' ? 'danger' : 'secondary')) ?>">
                                    <?= ucfirst($item['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-editar" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editarItemModal"
                                        data-id="<?= $item['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($item['nombre']) ?>"
                                        data-tipo="<?= $item['tipo'] ?>"
                                        data-cantidad="<?= $item['cantidad'] ?>"
                                        data-unidad="<?= $item['unidad'] ?>"
                                        data-fecha-vencimiento="<?= $item['fecha_vencimiento'] ?>"
                                        data-proveedor="<?= htmlspecialchars($item['proveedor'] ?? '') ?>"
                                        data-estado="<?= $item['estado'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="eliminar_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de querer eliminar este item?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay items en el inventario</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para nuevo item -->
<div class="modal fade" id="nuevoItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Item al Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="procesar_inventario.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="comida">Comida</option>
                            <option value="medicamento">Medicamento</option>
                            <option value="accesorio">Accesorio</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="unidad" class="form-label">Unidad</label>
                            <input type="text" class="form-control" id="unidad" name="unidad" value="unidades" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento (opcional)</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                    </div>
                    <div class="mb-3">
                        <label for="proveedor" class="form-label">Proveedor (opcional)</label>
                        <input type="text" class="form-control" id="proveedor" name="proveedor">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Guardar Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar item -->
<div class="modal fade" id="editarItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Item del Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="procesar_edicion.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="editar_id" name="id">
                    <div class="mb-3">
                        <label for="editar_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="editar_tipo" name="tipo" required>
                            <option value="comida">Comida</option>
                            <option value="medicamento">Medicamento</option>
                            <option value="accesorio">Accesorio</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editar_cantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="editar_cantidad" name="cantidad" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_unidad" class="form-label">Unidad</label>
                            <input type="text" class="form-control" id="editar_unidad" name="unidad" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_fecha_vencimiento" class="form-label">Fecha de Vencimiento (opcional)</label>
                        <input type="date" class="form-control" id="editar_fecha_vencimiento" name="fecha_vencimiento">
                    </div>
                    <div class="mb-3">
                        <label for="editar_proveedor" class="form-label">Proveedor (opcional)</label>
                        <input type="text" class="form-control" id="editar_proveedor" name="proveedor">
                    </div>
                    <div class="mb-3">
                        <label for="editar_estado" class="form-label">Estado</label>
                        <select class="form-select" id="editar_estado" name="estado" required>
                            <option value="disponible">Disponible</option>
                            <option value="bajo">Bajo</option>
                            <option value="agotado">Agotado</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar clic en botones de editar
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function() {
            // Obtener datos del botón
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const tipo = this.getAttribute('data-tipo');
            const cantidad = this.getAttribute('data-cantidad');
            const unidad = this.getAttribute('data-unidad');
            const fechaVencimiento = this.getAttribute('data-fecha-vencimiento');
            const proveedor = this.getAttribute('data-proveedor');
            const estado = this.getAttribute('data-estado');
            
            // Llenar el formulario de edición
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_nombre').value = nombre;
            document.getElementById('editar_tipo').value = tipo;
            document.getElementById('editar_cantidad').value = cantidad;
            document.getElementById('editar_unidad').value = unidad;
            document.getElementById('editar_fecha_vencimiento').value = fechaVencimiento;
            document.getElementById('editar_proveedor').value = proveedor;
            document.getElementById('editar_estado').value = estado;
        });
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>