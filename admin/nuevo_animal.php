<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/funciones.php';

// Verificar permisos - solo admin y voluntarios
if (!isset($_SESSION['usuario'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

if ($_SESSION['usuario']['rol'] != 'admin' && $_SESSION['usuario']['rol'] != 'voluntario') {
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Animal | PetLove</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2e8b57;
            --secondary-color: #ff8c42;
            --light-color: #f5f5dc;
        }
        
        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1f6b3d;
            border-color: #1f6b3d;
        }
        
        .header-title {
            color: var(--primary-color);
            border-bottom: 3px solid var(--secondary-color);
            display: inline-block;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/admin_header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h2 class="header-title">Registrar Nuevo Animal</h2>
                    </div>
                    <div class="card-body">
                        <?php mostrarMensajes(); ?>
                        
                        <form action="<?= SITE_URL ?>/procesar_nueva_adopcion.php" method="post" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre del Animal</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="especie" class="form-label">Especie</label>
                                    <select class="form-select" id="especie" name="especie" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="perro">Perro</option>
                                        <option value="gato">Gato</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="raza" class="form-label">Raza</label>
                                    <input type="text" class="form-control" id="raza" name="raza">
                                </div>
                                <div class="col-md-3">
                                    <label for="edad" class="form-label">Edad (años)</label>
                                    <input type="number" class="form-control" id="edad" name="edad" min="0" max="30">
                                </div>
                                <div class="col-md-3">
                                    <label for="tamano" class="form-label">Tamaño</label>
                                    <select class="form-select" id="tamano" name="tamano" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="pequeño">Pequeño</option>
                                        <option value="mediano">Mediano</option>
                                        <option value="grande">Grande</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sexo" class="form-label">Sexo</label>
                                    <select class="form-select" id="sexo" name="sexo" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="macho">Macho</option>
                                        <option value="hembra">Hembra</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="imagen" class="form-label">Foto del Animal</label>
                                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
                                    <small class="text-muted">Formatos: JPG, PNG, GIF (Máx. 2MB)</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Registrar Animal</button>
                                <a href="<?= SITE_URL ?>/admin/gestion_animales.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/admin_footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const imagen = document.getElementById('imagen').files[0];
            if (imagen && imagen.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('La imagen no debe superar los 2MB');
                return false;
            }
            
            // Puedes agregar más validaciones aquí si es necesario
            return true;
        });
    </script>
</body>
</html>