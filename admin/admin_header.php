<?php

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Incluir configuración y conexión a la base de datos
require_once '../includes/config.php';
require_once '../includes/database.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Huellitas en la Luna Admin - <?= $page_title ?? 'Panel' ?></title>
    <!-- En tu admin_header.php o antes del cierre de </head> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../assets/img/Logopetlove1.jpg" alt="Huellitas en la Luna" width="30" height="30" class="d-inline-block align-top">
                Huellitas en la Luna Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarAdmin">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion_inventario.php"><i class="bi bi-box-seam"></i> Inventario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion_adopciones.php"><i class="bi bi-heart"></i> Adopciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion_donaciones.php"><i class="bi bi-cash-stack"></i> Donaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion_usuarios.php"><i class="bi bi-people"></i> Usuarios</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= $_SESSION['usuario']['nombre'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person"></i> Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">