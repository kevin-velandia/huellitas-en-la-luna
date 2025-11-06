<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

// Verificar permisos de admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    redirect('../login.php');
}

// Incluir cabecera
require_once 'admin_header.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Registrar Nuevo Usuario</h2>
        </div>
        
        <div class="card-body">
            <form action="procesar_registro.php" method="POST" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <div class="invalid-feedback">Por favor ingresa un nombre válido</div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Por favor ingresa un email válido</div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>
                    <div class="col-md-6">
                        <label for="rol" class="form-label">Rol</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="">Seleccionar rol...</option>
                            <option value="admin">Administrador</option>
                            <option value="voluntario" selected>Voluntario</option>
                        </select>
                        <div class="invalid-feedback">Por favor selecciona un rol</div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres</div>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Las contraseñas no coinciden</div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="gestion_usuarios.php" class="btn btn-secondary me-md-2">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Registrar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación del formulario en el cliente
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(event) {
        // Validar contraseñas coincidan
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Las contraseñas no coinciden");
            confirmPassword.reportValidity();
            event.preventDefault();
            event.stopPropagation();
        } else {
            confirmPassword.setCustomValidity("");
        }
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php require_once 'admin_footer.php'; ?>