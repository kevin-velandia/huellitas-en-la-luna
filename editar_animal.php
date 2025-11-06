<?php
// Iniciar buffer de salida al principio
ob_start();

require_once 'includes/header.php';
require_once 'includes/database.php';
require_once 'includes/funciones.php';

if (!isset($_SESSION['usuario'])) {
    // Limpiar buffer antes de redireccionar
    ob_end_clean();
    header("Location: login.php");
    exit();
}

// Verificar que se haya proporcionado un ID de animal
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    ob_end_clean();
    header("Location: mis_adopciones.php");
    exit();
}

$animal_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario']['id'];

try {
    // Obtener información del animal
    $stmt = $conn->prepare("
        SELECT * FROM animales 
        WHERE id = ? AND (voluntario_id = ? OR ?)
    ");
    $stmt->bind_param("iii", $animal_id, $usuario_id, $_SESSION['usuario']['es_admin']);
    $stmt->execute();
    $animal = $stmt->get_result()->fetch_assoc();

    if (!$animal) {
        throw new Exception("Animal no encontrado o no tienes permiso para editarlo");
    }

    // Procesar el formulario si se envió
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre']);
        $especie = $_POST['especie'];
        $raza = trim($_POST['raza']);
        $edad = floatval($_POST['edad']);
        $tamano = $_POST['tamano'];
        $genero = $_POST['genero'];
        $descripcion = trim($_POST['descripcion']);
        $estado = $_POST['estado'];
        
        // Validaciones básicas
        if (empty($nombre) || empty($descripcion)) {
            throw new Exception("Nombre y descripción son campos obligatorios");
        }
        
        if ($edad <= 0) {
            throw new Exception("La edad debe ser un número positivo");
        }
        
        // Procesar imagen si se subió una nueva
        $foto = $animal['foto'];
        if (!empty($_FILES['foto']['name'])) {
            $uploadDir = 'uploads/adopciones/';
            $uploadFile = $uploadDir . basename($_FILES['foto']['name']);
            
            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['foto']['type'], $allowedTypes)) {
                throw new Exception("Solo se permiten imágenes JPG, PNG o GIF");
            }
            
            // Mover archivo subido
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadFile)) {
                $foto = basename($_FILES['foto']['name']);
                // Eliminar foto anterior si existe y es diferente
                if ($animal['foto'] && $animal['foto'] != $foto) {
                    @unlink($uploadDir . $animal['foto']);
                }
            } else {
                throw new Exception("Error al subir la imagen");
            }
        }
        
        // Actualizar en la base de datos
        $stmt = $conn->prepare("
            UPDATE animales 
            SET nombre = ?, especie = ?, raza = ?, edad = ?, tamano = ?, 
                genero = ?, descripcion = ?, estado = ?, foto = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssdsssssi", 
            $nombre, $especie, $raza, $edad, $tamano, 
            $genero, $descripcion, $estado, $foto, $animal_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['mensaje_exito'] = "Información del animal actualizada correctamente";
            ob_end_clean();
            header("Location: detalle_adopcion.php?id=" . $animal_id);
            exit();
        } else {
            throw new Exception("Error al actualizar el animal: " . $conn->error);
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Si llegamos aquí, mostrar el formulario
ob_end_flush();
?>

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
    
    .edit-form-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-top: 2rem;
    }
    
    .form-title {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--secondary-color);
        padding-bottom: 0.5rem;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        padding: 0.5rem;
        border-radius: 5px;
        border: 1px solid #ddd;
        margin-bottom: 1rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(46, 139, 87, 0.25);
    }
    
    .current-image {
        max-width: 200px;
        max-height: 200px;
        border-radius: 5px;
        margin-bottom: 1rem;
        border: 2px solid #eee;
    }
    
    .btn-submit {
        background-color: var(--primary-color);
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        transition: background-color 0.3s;
    }
    
    .btn-submit:hover {
        background-color: #1f6d3d;
    }
    
    .btn-cancel {
        background-color: #6c757d;
        color: white;
    }
    
    .error-message {
        color: #dc3545;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .edit-form-container {
            padding: 1rem;
        }
    }
</style>

<div class="container my-5">
    <h1>Editar información de <?= htmlspecialchars($animal['nombre']) ?></h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <div class="edit-form-container">
        <form method="POST" enctype="multipart/form-data">
            <h2 class="form-title">Información básica</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($animal['nombre']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="especie" class="form-label">Especie</label>
                        <select class="form-select" id="especie" name="especie" required>
                            <option value="perro" <?= $animal['especie'] == 'perro' ? 'selected' : '' ?>>Perro</option>
                            <option value="gato" <?= $animal['especie'] == 'gato' ? 'selected' : '' ?>>Gato</option>
                            <option value="otro" <?= $animal['especie'] == 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="raza" class="form-label">Raza</label>
                        <input type="text" class="form-control" id="raza" name="raza" 
                               value="<?= htmlspecialchars($animal['raza']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edad" class="form-label">Edad (años)</label>
                        <input type="number" step="0.1" class="form-control" id="edad" name="edad" 
                               value="<?= htmlspecialchars($animal['edad']) ?>" required min="0.1" max="30">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Imagen actual</label>
                        <div>
                            <img src="uploads/adopciones/<?= htmlspecialchars($animal['foto']) ?>" 
                                 class="current-image" 
                                 alt="<?= htmlspecialchars($animal['nombre']) ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto" class="form-label">Cambiar imagen</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                        <small class="text-muted">Formatos aceptados: JPG, PNG, GIF. Máx. 2MB</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="tamano" class="form-label">Tamaño</label>
                        <select class="form-select" id="tamano" name="tamano" required>
                            <option value="pequeño" <?= $animal['tamano'] == 'pequeño' ? 'selected' : '' ?>>Pequeño</option>
                            <option value="mediano" <?= $animal['tamano'] == 'mediano' ? 'selected' : '' ?>>Mediano</option>
                            <option value="grande" <?= $animal['tamano'] == 'grande' ? 'selected' : '' ?>>Grande</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="genero" class="form-label">Género</label>
                        <select class="form-select" id="genero" name="genero" required>
                            <option value="macho" <?= $animal['genero'] == 'macho' ? 'selected' : '' ?>>Macho</option>
                            <option value="hembra" <?= $animal['genero'] == 'hembra' ? 'selected' : '' ?>>Hembra</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="disponible" <?= $animal['estado'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                            <option value="en_proceso" <?= $animal['estado'] == 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                            <option value="no_disponible" <?= $animal['estado'] == 'no_disponible' ? 'selected' : '' ?>>No disponible</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" 
                          rows="5" required><?= htmlspecialchars($animal['descripcion']) ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="detalle_adopcion.php?id=<?= $animal['id'] ?>" class="btn btn-cancel">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-submit">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Validación del formulario antes de enviar
document.querySelector('form').addEventListener('submit', function(e) {
    const edad = parseFloat(document.getElementById('edad').value);
    if (edad <= 0 || edad > 30) {
        alert('La edad debe estar entre 0.1 y 30 años');
        e.preventDefault();
        return false;
    }
    
    const fileInput = document.getElementById('foto');
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
        if (fileSize > 2) {
            alert('El tamaño de la imagen no debe exceder 2MB');
            e.preventDefault();
            return false;
        }
    }
    
    return true;
});
</script>

<?php require_once 'includes/footer.php'; ?>