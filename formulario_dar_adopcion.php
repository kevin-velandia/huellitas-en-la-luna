<?php
session_start();
// Si quieres, puedes validar que el usuario esté logueado para registrar animales
if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Debes iniciar sesión para registrar un animal.";
    header("Location: login.php");
    exit;
}

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/header.php'; // o donde cargues el header
?>

<style>
    :root {
        --primary-color: #2e8b57;
        --secondary-color: #ff8c42;
        --dark-color: #333;
        --light-color: #f5f5dc;
        --success-color: #28a745;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        background-color: var(--light-color);
    }

    .btn {
        display: inline-block;
        background: var(--primary-color);
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

<div class="container py-5">
    <h1 class="text-center mb-4">Dar en Adopción un Animal</h1>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="procesar_nueva_adopcion.php" method="post" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Animal</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="especie" class="form-label">Especie</label>
                        <select class="form-select" id="especie" name="especie" required>
                            <option value="">Seleccione...</option>
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="raza" class="form-label">Raza</label>
                        <input type="text" class="form-control" id="raza" name="raza">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edad" class="form-label">Edad (años)</label>
                        <input type="number" class="form-control" id="edad" name="edad" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tamano" class="form-label">Tamaño</label>
                        <select class="form-select" id="tamano" name="tamano" required>
                            <option value="pequeño">Pequeño</option>
                            <option value="mediano">Mediano</option>
                            <option value="grande">Grande</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="genero" class="form-label">Género</label>
                        <select class="form-select" id="genero" name="genero" required>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="comportamiento" class="form-label">Comportamiento</label>
                    <input type="text" class="form-control" id="comportamiento" name="comportamiento">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vacunado" class="form-label">Vacunado</label>
                        <select class="form-select" id="vacunado" name="vacunado">
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="esterilizado" class="form-label">Esterilizado</label>
                        <select class="form-select" id="esterilizado" name="esterilizado">
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="foto" class="form-label">Foto del Animal</label>
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                    <small class="text-muted">Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Registrar Animal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
