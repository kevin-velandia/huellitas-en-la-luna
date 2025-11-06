<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/funciones.php';

// Verificar permisos de admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Obtener ID del animal a editar
$id_animal = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_animal <= 0) {
    $_SESSION['error'] = "ID de animal inválido";
    header("Location: gestion_adopciones.php");
    exit();
}

// Obtener datos del animal
$animal = obtenerAnimalPorId($id_animal);

if (!$animal) {
    $_SESSION['error'] = "Animal no encontrado";
    header("Location: gestion_adopciones.php");
    exit();
}

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = $conn->real_escape_string(trim($_POST['nombre']));
        $especie = $conn->real_escape_string(trim($_POST['especie']));
        $raza = $conn->real_escape_string(trim($_POST['raza']));
        $edad = (int)$_POST['edad'];
        $tamano = $conn->real_escape_string(trim($_POST['tamano']));
        $genero = $conn->real_escape_string(trim($_POST['genero']));
        $descripcion = $conn->real_escape_string(trim($_POST['descripcion']));
        $estado = $conn->real_escape_string(trim($_POST['estado']));
        $vacunado = isset($_POST['vacunado']) ? 'si' : 'no';
        $esterilizado = isset($_POST['esterilizado']) ? 'si' : 'no';
        $comportamiento = $conn->real_escape_string(trim($_POST['comportamiento']));

        // Procesar la foto si se subió una nueva
        if (!empty($_FILES['foto']['name'])) {
            $uploadDir = '../uploads/adopciones/';
            $fotoNombre = uniqid() . '_' . basename($_FILES['foto']['name']);
            $uploadFile = $uploadDir . $fotoNombre;
            
            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['foto']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Solo se permiten imágenes JPEG, PNG o GIF.");
            }
            
            // Mover el archivo
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadFile)) {
                // Eliminar la foto anterior si existe
                if (!empty($animal['foto']) && file_exists($uploadDir . $animal['foto'])) {
                    unlink($uploadDir . $animal['foto']);
                }
                
                // Actualizar el nombre de la foto en la base de datos
                $stmtFoto = $conn->prepare("UPDATE animales SET foto = ? WHERE id = ?");
                $stmtFoto->bind_param("si", $fotoNombre, $id_animal);
                $stmtFoto->execute();
                $stmtFoto->close();
            } else {
                throw new Exception("Error al subir la imagen.");
            }
        }

        // Actualizar datos del animal
        $stmt = $conn->prepare("UPDATE animales SET 
            nombre = ?,
            especie = ?,
            raza = ?,
            edad = ?,
            tamano = ?,
            genero = ?,
            descripcion = ?,
            estado = ?,
            vacunado = ?,
            esterilizado = ?,
            comportamiento = ?
            WHERE id = ?");

        $stmt->bind_param("sssisssssssi", 
            $nombre, $especie, $raza, $edad, $tamano, 
            $genero, $descripcion, $estado, $vacunado, 
            $esterilizado, $comportamiento, $id_animal);

        if ($stmt->execute()) {
            $_SESSION['exito'] = "Animal actualizado correctamente";
            header("Location: gestion_adopciones.php");
            exit();
        } else {
            throw new Exception("Error al actualizar el animal: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

include 'admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Editar Animal: <?= htmlspecialchars($animal['nombre']) ?></h1>
    
    <?php mostrarMensajes(); ?>
    
    <div class="card shadow">
        <div class="card-body">
            <form action="editar_animal.php?id=<?= $id_animal ?>" method="post" enctype="multipart/form-data">
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
                                <option value="perro" <?= $animal['especie'] === 'perro' ? 'selected' : '' ?>>Perro</option>
                                <option value="gato" <?= $animal['especie'] === 'gato' ? 'selected' : '' ?>>Gato</option>
                                <option value="otro" <?= $animal['especie'] === 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="raza" class="form-label">Raza</label>
                            <input type="text" class="form-control" id="raza" name="raza" 
                                   value="<?= htmlspecialchars($animal['raza']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edad" class="form-label">Edad (años)</label>
                            <input type="number" class="form-control" id="edad" name="edad" 
                                   value="<?= htmlspecialchars($animal['edad']) ?>" min="0" max="30">
                        </div>
                        
                        <div class="mb-3">
                            <label for="tamano" class="form-label">Tamaño</label>
                            <select class="form-select" id="tamano" name="tamano">
                                <option value="">Seleccione...</option>
                                <option value="pequeño" <?= $animal['tamano'] === 'pequeño' ? 'selected' : '' ?>>Pequeño</option>
                                <option value="mediano" <?= $animal['tamano'] === 'mediano' ? 'selected' : '' ?>>Mediano</option>
                                <option value="grande" <?= $animal['tamano'] === 'grande' ? 'selected' : '' ?>>Grande</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="genero" class="form-label">Género</label>
                            <select class="form-select" id="genero" name="genero">
                                <option value="">Seleccione...</option>
                                <option value="macho" <?= $animal['genero'] === 'macho' ? 'selected' : '' ?>>Macho</option>
                                <option value="hembra" <?= $animal['genero'] === 'hembra' ? 'selected' : '' ?>>Hembra</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="disponible" <?= $animal['estado'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                <option value="en_proceso" <?= $animal['estado'] === 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                                <option value="adoptado" <?= $animal['estado'] === 'adoptado' ? 'selected' : '' ?>>Adoptado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="vacunado" name="vacunado" 
                                   <?= $animal['vacunado'] === 'si' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="vacunado">Vacunado</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="esterilizado" name="esterilizado" 
                                   <?= $animal['esterilizado'] === 'si' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="esterilizado">Esterilizado</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comportamiento" class="form-label">Comportamiento</label>
                            <input type="text" class="form-control" id="comportamiento" name="comportamiento" 
                                   value="<?= htmlspecialchars($animal['comportamiento']) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($animal['descripcion']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="foto" class="form-label">Foto actual</label>
                    <?php if ($animal['foto']): ?>
                        <div class="mb-2">
                            <img src="../uploads/adopciones/<?= htmlspecialchars($animal['foto']) ?>" 
                                 alt="<?= htmlspecialchars($animal['nombre']) ?>" 
                                 class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay foto disponible</p>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                    <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="gestion_adopciones.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>