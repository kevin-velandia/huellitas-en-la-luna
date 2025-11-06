<?php
require_once 'includes/header.php';
require_once 'includes/database.php';

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    die("Error en la conexión a la base de datos");
}

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];

try {
    // Obtener donaciones monetarias
    $stmt_monetarias = $conn->prepare("SELECT * FROM donaciones_monetarias 
                                     WHERE id_donante = ? 
                                     ORDER BY fecha_donacion DESC");
    if (!$stmt_monetarias) {
        throw new Exception("Error al preparar consulta: " . $conn->error);
    }
    
    $stmt_monetarias->bind_param("i", $id_usuario);
    $stmt_monetarias->execute();
    $monetarias = $stmt_monetarias->get_result();

    // Obtener donaciones en especie
    $stmt_especie = $conn->prepare("SELECT * FROM donaciones_especie 
                                   WHERE id_donante = ? 
                                   ORDER BY fecha_donacion DESC");
    if (!$stmt_especie) {
        throw new Exception("Error al preparar consulta: " . $conn->error);
    }
    
    $stmt_especie->bind_param("i", $id_usuario);
    $stmt_especie->execute();
    $especie = $stmt_especie->get_result();

} catch (Exception $e) {
    // Mostrar error amigable
    die("<div class='alert alert-danger'>Error al cargar donaciones: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>

<style>
     :root {
        --primary-color: #2e8b57; /* Verde bosque */
        --secondary-color: #ff8c42; /* Naranja cálido */
        --dark-color: #333;
        --light-color: #f5f5dc; /* Beige claro */
        --success-color: #28a745;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--dark-color);
        background-color: var(--light-color); /* Changed to beige claro */
    }

    .btn {
        display: inline-block;
        background: var(--primary-color); /* Verde bosque */
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
</style>

<!-- Resto de tu HTML -->
<div class="container my-5">
    <h1 class="mb-4">Mis Donaciones</h1>
    
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="monetarias-tab" data-bs-toggle="tab" data-bs-target="#monetarias">
                <i class="bi bi-cash-coin"></i> Monetarias
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="especie-tab" data-bs-toggle="tab" data-bs-target="#especie">
                <i class="bi bi-box-seam"></i> En Especie
            </button>
        </li>
    </ul>
    
    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white">
        <div class="tab-pane fade show active" id="monetarias">
            <?php if ($monetarias->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($donacion = $monetarias->fetch_assoc()): ?>
                            <tr>
                                <td>$<?= number_format($donacion['monto'], 2) ?></td>
                                <td><?= ucfirst($donacion['metodo_pago']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $donacion['estado'] == 'aprobada' ? 'success' : 
                                        ($donacion['estado'] == 'pendiente' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($donacion['estado']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No has realizado donaciones monetarias aún.</div>
            <?php endif; ?>
        </div>
        
        <div class="tab-pane fade" id="especie">
            <?php if ($especie->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($donacion = $especie->fetch_assoc()): ?>
                            <tr>
                                <td><?= ucfirst($donacion['tipo']) ?></td>
                                <td><?= htmlspecialchars($donacion['descripcion']) ?></td>
                                <td><?= $donacion['cantidad'] ?> <?= $donacion['unidad'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $donacion['estado'] == 'recibida' ? 'success' : 
                                        ($donacion['estado'] == 'pendiente' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($donacion['estado']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No has realizado donaciones en especie aún.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>