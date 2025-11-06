<?php
session_start();

// Verificar autenticación y rol de admin
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Incluir configuración y conexión a la base de datos
require_once '../includes/config.php';
require_once '../includes/database.php';

// Obtener datos actualizados del usuario
$usuario_id = $_SESSION['usuario']['id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Actualizar datos en sesión
$_SESSION['usuario'] = $usuario;

// Establecer título de página
$page_title = "Perfil de Usuario";

// Incluir cabecera
require_once 'admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="../assets/img/perfil.jpg" alt="Foto de perfil" class="rounded-circle mb-3" width="150">
                    <h4><?= htmlspecialchars($usuario['nombre']) ?></h4>
                    <span class="badge bg-<?= $usuario['rol'] === 'admin' ? 'primary' : 'success' ?>">
                        <?= ucfirst($usuario['rol']) ?>
                    </span>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Cambiar contraseña</h5>
                </div>
                <div class="card-body">
                    <form action="procesar_cambio_password.php" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Actualizar contraseña</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Información del perfil</h5>
                    <a href="editar_perfil.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" value="<?= $usuario['telefono'] ?? 'No especificado' ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <input type="text" class="form-control" value="<?= ucfirst($usuario['rol']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de registro</label>
                                <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Último acceso</label>
                                <input type="text" class="form-control" value="<?= isset($usuario['ultimo_acceso']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca' ?>" readonly>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Actividad reciente</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-box-seam"></i> Actualizó 3 items en el inventario</span>
                            <small class="text-muted">Hace 2 horas</small>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person-plus"></i> Registró un nuevo usuario</span>
                            <small class="text-muted">Ayer</small>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-heart"></i> Aprobó 2 solicitudes de adopción</span>
                            <small class="text-muted">Hace 3 días</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Incluir pie de página
require_once 'admin_footer.php';
?>