<?php 
session_start();
require_once 'includes/database.php';
require_once 'includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = Database::getInstance();
        $conn = $database->getConnection();
        
        $email = $_POST['email'];
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND rol = ?");
        $stmt->bind_param("ss", $email, $rol);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            if (password_verify($password, $usuario['password'])) {
                $_SESSION['usuario'] = $usuario;
                if ($rol === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Contraseña incorrecta";
            }
        } else {
            $_SESSION['error'] = "Usuario no encontrado o no tiene el rol seleccionado";
        }
        header("Location: login.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en el sistema";
        error_log("Login error: " . $e->getMessage());
        header("Location: login.php");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetLove - Iniciar Sesión</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
</head>
<body>
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
    
    <main class="login-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card login-card shadow-lg">
                        <div class="card-header bg-success text-white text-center py-4">
                            <img src="assets/img/LogoPetlove1.jpg" alt="PetLove" width="100" height="100" class="rounded-circle mb-3 border border-3 border-white">
                            <h2><i class="bi bi-heart-fill"></i> Iniciar Sesión en Huellitas en la luna</h2>
                        </div>
                        
                        <div class="card-body p-5">
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>
                            
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-bold">
                                        <i class="bi bi-envelope-fill me-2"></i>Correo Electrónico
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope text-success"></i>
                                        </span>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                               placeholder="tucorreo@ejemplo.com" required
                                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                        <div class="invalid-feedback">
                                            Por favor ingresa un correo electrónico válido
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-bold">
                                        <i class="bi bi-lock-fill me-2"></i>Contraseña
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-key text-success"></i>
                                        </span>
                                        <input type="password" class="form-control form-control-lg" id="password" 
                                               name="password" placeholder="••••••••" required
                                               minlength="8">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Mostrar/Ocultar">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <div class="invalid-feedback">
                                            La contraseña debe tener al menos 8 caracteres
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="rol" class="form-label fw-bold">
                                        <i class="bi bi-person-rolodex me-2"></i>Tipo de usuario
                                    </label>
                                    <select class="form-select form-control-lg" id="rol" name="rol" required>
                                        <option value="" disabled selected>Seleccione un rol</option>
                                        <option value="voluntario" <?= (isset($_POST['rol']) && $_POST['rol'] === 'voluntario') ? 'selected' : '' ?>>Voluntario</option>
                                        <option value="admin" <?= (isset($_POST['rol']) && $_POST['rol'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor selecciona un rol
                                    </div>
                                </div>
                                
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-petlove btn-lg py-3">
                                        <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                                    </button>
                                
                                <div class="text-center">
                                    <p class="mb-0">¿No tienes cuenta? 
                                        <a href="registro.php" class="text-success fw-bold">
                                            <i class="bi bi-person-plus"></i> Regístrate como voluntario
                                        </a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Función para mostrar/ocultar contraseña
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const passwordInput = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('bi-eye-fill');
                        icon.classList.add('bi-eye-slash-fill');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('bi-eye-slash-fill');
                        icon.classList.add('bi-eye-fill');
                    }
                });
            });

            // Validación de formulario
            (function () {
                'use strict'
                
                const forms = document.querySelectorAll('.needs-validation')
                
                Array.from(forms).forEach(form => {
                    form.addEventListener('submit', event => {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
            })()
        });
    </script>
</body>
</html>

<?php require_once 'includes/footer.php'; ?>