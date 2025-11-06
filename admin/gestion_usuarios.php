<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

// Verificar autenticación y rol de admin
if (!isset($_SESSION['usuario'])) {
    redirect('../login.php');
}

if ($_SESSION['usuario']['rol'] != 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    redirect('../index.php');
}

// Obtener usuarios
$sql = "SELECT * FROM usuarios ORDER BY rol, nombre";
$usuarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Procesar cambio de rol
if (isset($_GET['cambiar_rol'])) {
    $id = (int)$_GET['id'];
    $nuevo_rol = $_GET['cambiar_rol'] === 'admin' ? 'admin' : 'voluntario';
    
    $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_rol, $id);
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Rol actualizado correctamente";
        redirect('gestion_usuarios.php');
    } else {
        $_SESSION['error'] = "Error al actualizar el rol";
    }
}

// Procesar eliminación de usuario
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['id'];
    
    // No permitir eliminarse a sí mismo
    if ($id == $_SESSION['usuario']['id']) {
        $_SESSION['error'] = "No puedes eliminarte a ti mismo";
        redirect('gestion_usuarios.php');
    }
    
    try {
        // Iniciar transacción
        $conn->begin_transaction();
        
        // 1. Eliminar registros relacionados primero (si existen)
        // Ejemplo si hay tablas relacionadas:
        // $conn->query("DELETE FROM tabla_relacionada WHERE usuario_id = $id");
        
        // 2. Eliminar el usuario
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $conn->commit();
                $_SESSION['exito'] = "Usuario eliminado correctamente";
            } else {
                $conn->rollback();
                $_SESSION['error'] = "No se encontró el usuario con ID $id";
            }
        } else {
            $conn->rollback();
            $_SESSION['error'] = "Error al eliminar el usuario: " . $conn->error;
        }
        
        redirect('gestion_usuarios.php');
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error al eliminar: " . $e->getMessage();
        redirect('gestion_usuarios.php');
    }
}
?>

<?php require_once 'admin_header.php'; ?>

<div class="container mt-4">
    <?php if (isset($_SESSION['exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['exito'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['exito']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h5 mb-0">Gestión de Usuarios</h2>
            <a href="registro_usuario.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo Usuario
            </a>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover admin-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= $usuario['id'] ?></td>
                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= $usuario['telefono'] ?? '-' ?></td>
                            <td>
                                <span class="badge bg-<?= $usuario['rol'] === 'admin' ? 'primary' : 'success' ?>">
                                    <?= ucfirst($usuario['rol']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <?php if ($usuario['rol'] == 'voluntario'): ?>
                                        <a href="?cambiar_rol=admin&id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-person-check"></i> Admin
                                        </a>
                                    <?php else: ?>
                                        <a href="?cambiar_rol=voluntario&id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-person"></i> Voluntario
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($usuario['id'] != $_SESSION['usuario']['id']): ?>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $usuario['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        
                                        <!-- Modal de confirmación -->
                                        <div class="modal fade" id="deleteModal<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar eliminación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        ¿Estás seguro de eliminar al usuario <?= htmlspecialchars($usuario['nombre']) ?>?
                                                        <div class="mt-3 alert alert-warning">
                                                            <i class="bi bi-exclamation-triangle"></i> Esta acción no se puede deshacer
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <a href="?eliminar=1&id=<?= $usuario['id'] ?>" class="btn btn-danger">
                                                            <i class="bi bi-trash"></i> Confirmar Eliminación
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
</div>

<?php require_once 'admin_footer.php'; ?>