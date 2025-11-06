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

// 4. Verificación de permisos
if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Debes iniciar sesión";
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['usuario']['rol'] != 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header("Location: ../index.php");
    exit();
}

// 5. Validación de parámetros GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$tipo = $_GET['tipo'] ?? '';
$tipo = htmlspecialchars($tipo, ENT_QUOTES | ENT_HTML5, 'UTF-8');

if (!$id || !in_array($tipo, ['monetaria', 'especie'])) {
    $_SESSION['error'] = "Parámetros inválidos";
    header("Location: gestion_donaciones.php");
    exit();
}

// 6. Conexión a base de datos con verificación
if (!isset($conn)) {
    $_SESSION['error'] = "Error de conexión a la base de datos";
    header("Location: gestion_donaciones.php");
    exit();
}

// 7. Obtener datos de la donación con manejo mejorado de errores
try {
    if ($tipo === 'monetaria') {
        $query = "SELECT d.*, u.nombre as donante 
                 FROM donaciones_monetarias d
                 JOIN usuarios u ON d.id_donante = u.id
                 WHERE d.id = ?";
        $estados_posibles = ['pendiente', 'aprobada', 'rechazada'];
        $titulo = "Editar Donación Monetaria";
    } else {
        $query = "SELECT d.*, u.nombre as donante 
                 FROM donaciones_especie d
                 JOIN usuarios u ON d.id_donante = u.id
                 WHERE d.id = ?";
        $estados_posibles = ['pendiente', 'recibida', 'rechazada', 'procesada'];
        $titulo = "Editar Donación en Especie";
    }

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
        throw new Exception("No se encontró la donación con ID $id");
    }

    // Conversión segura de valores numéricos
    if ($tipo === 'monetaria') {
        $donacion['monto'] = is_numeric($donacion['monto']) ? (float)$donacion['monto'] : 0.00;
    }

} catch (Exception $e) {
    error_log("Error en editar_donacion.php: " . $e->getMessage());
    $_SESSION['error'] = "Error al cargar datos: " . $e->getMessage();
    header("Location: gestion_donaciones.php");
    exit();
}
// 8. Procesamiento del formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Depuración: Registrar datos POST recibidos
    error_log("Datos POST recibidos: " . print_r($_POST, true));
    
    $estado = htmlspecialchars($_POST['estado'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $observaciones = htmlspecialchars($_POST['observaciones'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

    try {
        if (!in_array($estado, $estados_posibles)) {
            throw new Exception("Estado seleccionado no es válido");
        }

        // Iniciar transacción para operaciones atómicas
        $conn->begin_transaction();

        // CORRECCIÓN 1: Nombre correcto de la tabla (añadiendo la 's' faltante)
        $table_name = ($tipo === 'monetaria') ? 'donaciones_monetarias' : 'donaciones_especie';
        
        // CORRECCIÓN 2: Eliminando fecha_actualizacion si no existe en la tabla
        $update_query = "UPDATE $table_name 
                        SET estado = ?, observaciones = ? 
                        WHERE id = ?";
        
        error_log("Ejecutando consulta: $update_query con valores: $estado, $observaciones, $id");
        
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            throw new Exception("Error al preparar actualización: " . $conn->error);
        }

        $stmt->bind_param("ssi", $estado, $observaciones, $id);
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar: " . $stmt->error);
        }

        // Verificar si realmente se actualizó algún registro
        if ($stmt->affected_rows === 0) {
            throw new Exception("No se actualizó ningún registro. ¿Existe el ID $id?");
        }

        // Registro en historial
        $historial_query = "INSERT INTO historial_donaciones 
                          (id_donacion, tipo, id_usuario, accion, detalles, fecha) 
                          VALUES (?, ?, ?, 'edicion', ?, NOW())";
        
        $stmt_historial = $conn->prepare($historial_query);
        if (!$stmt_historial) {
            throw new Exception("Error al preparar historial: " . $conn->error);
        }

        $detalles = "Actualización: Estado cambiado a $estado";
        $id_usuario = $_SESSION['usuario']['id'];
        $stmt_historial->bind_param("isis", $id, $tipo, $id_usuario, $detalles);
        if (!$stmt_historial->execute()) {
            throw new Exception("Error al registrar en historial: " . $stmt_historial->error);
        }

        // Confirmar transacción
        $conn->commit();

        $_SESSION['exito'] = "Donación actualizada correctamente";
        error_log("Redirigiendo a detalle_donacion.php?tipo=$tipo&id=$id");
        header("Location: detalle_donacion.php?tipo=$tipo&id=$id");
        exit();

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->rollback();
        }
        error_log("Error al procesar formulario: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
}


// 9. Incluir header después de toda la lógica PHP
require_once __DIR__ . '/admin_header.php';
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string)$_SESSION['error'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0"><?= htmlspecialchars((string)$titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h2>
        </div>
        
        <div class="card-body">
            <form method="post" id="formEditarDonacion">
                <input type="hidden" name="id" value="<?= (int)$id ?>">
                <input type="hidden" name="tipo" value="<?= htmlspecialchars((string)$tipo, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4 class="mb-3">Información Básica</h4>
                        <div class="card">
                            <div class="card-body">
                                <p><strong>ID:</strong> <?= (int)$donacion['id'] ?></p>
                                
                                <p><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$donacion['fecha_donacion'])), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                                <p><strong>Donante:</strong> <?= htmlspecialchars((string)$donacion['donante'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                                
                                <?php if ($tipo === 'monetaria'): ?>
                                    <p><strong>Monto:</strong> $<?= htmlspecialchars(number_format((float)$donacion['monto'], 2), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                                    <p><strong>Método de pago:</strong> <?= htmlspecialchars(ucfirst((string)$donacion['metodo_pago']), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                                <?php else: ?>
                                    <p><strong>Tipo:</strong> <?= htmlspecialchars(ucfirst((string)$donacion['tipo']), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                                    <p><strong>Cantidad:</strong> <?= (int)$donacion['cantidad'] ?> <?= htmlspecialchars((string)$donacion['unidad'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h4 class="mb-3">Editar Estado</h4>
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <?php foreach ($estados_posibles as $estado_opcion): ?>
                                            <option value="<?= htmlspecialchars((string)$estado_opcion, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" <?= $donacion['estado'] === $estado_opcion ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucfirst((string)$estado_opcion), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars((string)$donacion['observaciones'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($tipo === 'especie'): ?>
                <div class="mb-4">
                    <h4 class="mb-3">Descripción Original</h4>
                    <div class="card">
                        <div class="card-body">
                            <?= nl2br(htmlspecialchars((string)$donacion['descripcion'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between">
                    <a href="detalle_donacion.php?tipo=<?= htmlspecialchars((string)$tipo, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>&id=<?= (int)$id ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>