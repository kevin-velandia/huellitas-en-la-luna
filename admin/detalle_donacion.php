<?php
// 1. Configuración inicial robusta
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 2. Manejo de sesión seguro
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Rutas absolutas para includes
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/funciones.php';

// 4. Validación de parámetros GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$tipo = $_GET['tipo'] ?? '';
$tipo = htmlspecialchars($tipo, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// Mapear tipo a tabla correcta
$tablas = [
    'monetaria' => 'donaciones_monetarias',
    'especie' => 'donaciones_especie'
];

if ($id === false || $id === null || !array_key_exists($tipo, $tablas)) {
    $_SESSION['error'] = "Parámetros inválidos";
    header("Location: gestion_donaciones.php");
    exit();
}

$tabla = $tablas[$tipo];

// 5. Verificación de permisos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header("Location: ../login.php");
    exit();
}

// 6. Verificación de conexión a DB
if (!isset($conn)) {
    $_SESSION['error'] = "Error de conexión a la base de datos";
    header("Location: gestion_donaciones.php");
    exit();
}

try {
    // Verificar existencia del registro primero
    $check_query = "SELECT id FROM {$tabla} WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    if (!$check_stmt) {
        throw new Exception("Error al preparar verificación: " . $conn->error);
    }
    $check_stmt->bind_param("i", $id);
    if (!$check_stmt->execute()) {
        throw new Exception("Error al verificar existencia: " . $check_stmt->error);
    }
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        throw new Exception("No existe una donación con ID {$id} en la tabla {$tabla}");
    }

    // Consulta principal para obtener datos
    $query = "SELECT d.*, u.nombre AS donante, u.email, u.telefono 
              FROM {$tabla} d
              LEFT JOIN usuarios u ON d.id_donante = u.id
              WHERE d.id = ?";

    $titulo = $tipo === 'monetaria' ? "Detalle de Donación Monetaria" : "Detalle de Donación en Especie";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar consulta: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $donacion = $result->fetch_assoc();

    if (!$donacion) {
        throw new Exception("No se encontraron datos para la donación con ID {$id}");
    }

    // Conversión segura de valores
    $donacion['id'] = (string)$donacion['id'];
    if ($tipo === 'monetaria') {
        $donacion['monto'] = (float)str_replace(',', '', $donacion['monto']);
    }
} catch (Exception $e) {
    error_log("Error en detalle_donacion.php: " . $e->getMessage());
    $_SESSION['error'] = "Error al cargar datos: " . $e->getMessage();
    header("Location: gestion_donaciones.php");
    exit();
}

// 7. Mapeo de estados para badges, según tu sistema original
$estado = $donacion['estado'] ?? '';
$estados_clases = [
    'recibido' => 'success',
    'aprobada' => 'success',
    'pendiente' => 'warning',
    'rechazado' => 'danger',
    'procesado' => 'primary'
];
$estado_class = $estados_clases[$estado] ?? 'secondary';

// 8. Incluir header después de toda la lógica PHP
require_once __DIR__ . '/admin_header.php';
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0"><?= htmlspecialchars($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h2>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4 class="mb-3">Información del Donante</h4>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($donacion['donante'] ?? 'Anónimo', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($donacion['email'] ?? 'No registrado', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($donacion['telefono'] ?? 'No registrado', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4 class="mb-3">Detalles de la Donación</h4>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>ID:</strong> <?= htmlspecialchars($donacion['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                            <p><strong>Fecha:</strong> 
                                <?= isset($donacion['fecha_donacion']) ? 
                                   htmlspecialchars(date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])), ENT_QUOTES | ENT_HTML5, 'UTF-8') : 
                                   'No especificada' ?>
                            </p>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-<?= $estado_class ?>">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $estado)), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                </span>
                            </p>

                            <?php if ($tipo === 'monetaria'): ?>
                                <p><strong>Monto:</strong> $<?= number_format((float)$donacion['monto'], 2) ?></p>
                                <p><strong>Método de pago:</strong> <?= htmlspecialchars(ucfirst($donacion['metodo_pago'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                            <?php else: ?>
                                <p><strong>Tipo:</strong> <?= htmlspecialchars(ucfirst($donacion['tipo'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                                <p><strong>Cantidad:</strong> <?= (int)($donacion['cantidad'] ?? 0) ?> <?= htmlspecialchars($donacion['unidad'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($tipo === 'especie'): ?>
            <div class="mb-4">
                <h4 class="mb-3">Descripción Completa</h4>
                <div class="card">
                    <div class="card-body">
                        <?= isset($donacion['descripcion']) ? nl2br(htmlspecialchars($donacion['descripcion'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : 'No hay descripción' ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($donacion['comprobante'])): ?>
            <div class="mb-4">
                <h4 class="mb-3">Comprobante</h4>
                <div class="card">
                    <div class="card-body text-center">
                        <a href="../assets/uploads/donaciones/<?= htmlspecialchars($donacion['comprobante'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" 
                           target="_blank" class="btn btn-primary">
                           <i class="bi bi-download"></i> Descargar Comprobante
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($donacion['observaciones'])): ?>
            <div class="mb-4">
                <h4 class="mb-3">Observaciones</h4>
                <div class="card">
                    <div class="card-body">
                        <?= nl2br(htmlspecialchars($donacion['observaciones'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between">
                <a href="gestion_donaciones.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <a href="editar_donacion.php?tipo=<?= htmlspecialchars($tipo, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>&id=<?= (int)$id ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar Donación
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
