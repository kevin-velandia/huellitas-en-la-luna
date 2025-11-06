<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Incluir configuración y conexión a la base de datos
require_once '../includes/config.php';
require_once '../includes/database.php';

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario']['id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Establecer título de página
$page_title = "Editar Perfil";

// Incluir cabecera
require_once 'admin_header.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Editar Perfil</h2>
        </div>
        
        <div class="card-body">
            <form action="procesar_editar_perfil.php" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                               value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="foto" class="form-label">Foto de Perfil</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                        <small class="text-muted">Formatos: JPG, PNG (Máx. 2MB)</small>
                    </div>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="perfil.php" class="btn btn-secondary me-md-2">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Incluir pie de página
require_once 'admin_footer.php';
?>