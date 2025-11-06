<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' : '' ?>Huellitas en la luna Fundación</title>
        
    <!-- jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Luego Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/estilo.css">
    
    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.ico">
    <style>
       
        .navbar-custom {
            position: relative;
            z-index: 1000;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
      
        main {
            position: relative;
            z-index: 1;
            margin-top: 0;
        }
    </style>
    <script>
// Limpiar modales al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Eliminar backdrops persistentes
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    // Restablecer el body
    document.body.classList.remove('modal-open');
    document.body.style.paddingRight = '';
    // Ocultar todos los modales
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('show');
    });
});
</script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/Logopetlove1.jpg" alt="Huellitas en la luna" width="60" height="60" class="rounded-circle">
                <span class="ms-2">Huelitas en la luna</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-success me-2" href="index.php">
                            <i class="bi bi-house-heart"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-success me-2" href="adopciones.php">
                            <i class="bi bi-heart"></i> Adopciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-success me-2" href="donaciones.php">
                            <i class="bi bi-cash-coin"></i> Donaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-success me-2" href="voluntarios.php">
                            <i class="bi bi-people"></i> Voluntarios
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    <?php if(isset($_SESSION['usuario'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if($_SESSION['usuario']['rol'] === 'voluntario'): ?>
                                    <li><a class="dropdown-item" href="mis_donaciones.php"><i class="bi bi-gift"></i> Mis Donaciones</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="mis_adopciones.php"><i class="bi bi-house-heart"></i> Mis Adopciones</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="formulario_dar_adopcion.php"><i class="bi bi-heart"></i> Dar en Adopción</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-success">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="container-fluid p-0">