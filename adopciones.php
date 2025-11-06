<?php
require_once 'includes/header.php';
require_once 'includes/funciones.php';

// Obtener parámetros de filtrado
$especie = $_GET['especie'] ?? '';
$edad = $_GET['edad'] ?? '';
$tamano = $_GET['tamano'] ?? '';
$genero = $_GET['genero'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

// Obtener animales disponibles con filtros
$animales = listarAnimalesDisponiblesFiltrados($especie, $edad, $tamano, $genero, $busqueda);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animales para Adopción | PetLove</title>
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
            color: var(--dark-color);
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

        .main-content {
            padding: 3rem 0;
        }

        h1, h2, h3 {
            font-family: 'Roboto Slab', serif;
            font-weight: 700;
        }

        h1 {
            color: var(--primary);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }

        h1::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: var(--secondary);
            margin: 10px auto;
        }

        .animales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .animal-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .animal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .animal-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .animal-info {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .animal-card h3 {
            color: var(--primary);
            margin: 0 0 0.5rem;
        }

        .animal-card p {
            margin: 0.3rem 0;
            color: var(--gray);
        }

        .btn-adoptar {
            margin-top: auto;
            display: flex;
            justify-content: center;
            padding-top: 1rem;
        }

        .btn-adoptar button {
            width: 100%;
            max-width: 200px;
        }

        .btn-adoptar:hover {
            background: #ff5252;
            color: white;
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        select, input[type="text"] {
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-family: inherit;
        }

        @media (max-width: 768px) {
            .animales-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="container">
            <h1>Animales Disponibles para Adopción</h1>
            
            <section class="filter-section">
                <h2>Filtrar Búsqueda</h2>
                <div class="filter-group">
                    <div>
                        <label for="especie" class="filter-label">Especie</label>
                        <select id="especie" name="especie">
                            <option value="">Todas</option>
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="otro">Otros</option>
                        </select>
                    </div>
                    <div>
                        <label for="edad" class="filter-label">Edad</label>
                        <select id="edad" name="edad">
                            <option value="">Cualquier edad</option>
                            <option value="cachorro">Cachorro (0-1 año)</option>
                            <option value="joven">Joven (1-5 años)</option>
                            <option value="adulto">Adulto (5+ años)</option>
                        </select>
                    </div>          
                    <div>
                        <label for="tamano" class="filter-label">Tamaño</label>
                        <select id="tamano" name="tamano">
                            <option value="">Cualquier tamaño</option>
                            <option value="pequeño">Pequeño</option>
                            <option value="mediano">Mediano</option>
                            <option value="grande">Grande</option>
                        </select>
                    </div>       
                    <div>
                        <label for="genero" class="filter-label">Género</label>
                        <select id="genero" name="genero">
                            <option value="">Cualquiera</option>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                        </select>
                    </div>
                </div> 
                <div>
                    <label for="busqueda" class="filter-label">Buscar por nombre o raza</label>
                    <input type="text" id="busqueda" name="busqueda" placeholder="Ej: Labrador, Max, etc.">
                </div>
            </section>

            <div class="animales-grid">
                <?php foreach ($animales as $animal): ?>
                <article class="animal-card">
                    <img src="uploads/adopciones/<?= htmlspecialchars($animal['foto']) ?>" alt="<?= htmlspecialchars($animal['nombre']) ?>">
                    <div class="animal-info">
                        <h3><?= htmlspecialchars($animal['nombre']) ?></h3>
                        <p><strong>Especie:</strong> <?= htmlspecialchars($animal['especie']) ?></p>
                        <p><strong>Edad:</strong> <?= htmlspecialchars($animal['edad']) ?> años</p>
                        <p><strong>Raza:</strong> <?= htmlspecialchars($animal['raza']) ?></p>
                        <p><strong>Tamaño:</strong> <?= htmlspecialchars($animal['tamano']) ?></p>
                        <p><strong>Género:</strong> <?= htmlspecialchars($animal['genero']) ?></p>
                        <p><?= htmlspecialchars($animal['descripcion']) ?></p>
                        
                        <div class="btn-adoptar">
                            <?php if(isset($_SESSION['usuario'])): ?>
                                <button onclick="mostrarFormularioAdopcion(<?= $animal['id'] ?>, '<?= htmlspecialchars($animal['nombre']) ?>')" 
                                        class="btn btn-primary">
                                    Quiero adoptar
                                </button>
                            <?php else: ?>
                                <button onclick="mostrarLoginNecesario()" class="btn btn-primary">
                                    Quiero adoptar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Modal de Adopción -->
    <div id="modalAdopcion" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Solicitud de Adopción</h2>
            <form id="formAdopcion" action="procesar_adopcion.php" method="post">
                <input type="hidden" name="animal_id" id="animal_id">
                <input type="hidden" name="usuario_id" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
                
                <div class="form-group">
                    <label>Estás solicitando adoptar a: <strong id="nombreAnimalModal"></strong></label>
                </div>
                
                <div class="form-group">
                    <label for="motivo">¿Por qué quieres adoptar esta mascota?</label>
                    <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="experiencia">¿Tienes experiencia con mascotas?</label>
                    <textarea class="form-control" id="experiencia" name="experiencia" rows="2" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="hogar">Describe tu hogar y espacio para la mascota</label>
                    <textarea class="form-control" id="hogar" name="hogar" rows="2" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="metodo_contacto">Método de contacto preferido:</label>
                    <select class="form-control" id="metodo_contacto" name="metodo_contacto" required>
                        <option value="email">Email</option>
                        <option value="telefono">Teléfono</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="presencial">Visita presencial</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <input type="checkbox" id="acepto_terminos" name="acepto_terminos" required>
                    <label for="acepto_terminos">Acepto los términos y condiciones de adopción</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
            </form>
        </div>
    </div>

    <script>
        // Mostrar mensaje de login necesario
        function mostrarLoginNecesario() {
            alert("Para adoptar una mascota, primero debes iniciar sesión. Serás redirigido a la página de login.");
            window.location.href = "login.php?redirect=" + encodeURIComponent(window.location.pathname);
        }
        
        // Mostrar formulario de adopción
        function mostrarFormularioAdopcion(animalId, animalNombre) {
            document.getElementById('animal_id').value = animalId;
            document.getElementById('nombreAnimalModal').textContent = animalNombre;
            document.getElementById('modalAdopcion').style.display = 'block';
        }
        
        // Cerrar modal
        function cerrarModal() {
            document.getElementById('modalAdopcion').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target == document.getElementById('modalAdopcion')) {
                cerrarModal();
            }
        }

        // Filtrar animales con AJAX
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#especie, #edad, #tamano, #genero, #busqueda').forEach(element => {
                element.addEventListener('change', filtrarAnimales);
            });
            
            function filtrarAnimales() {
                const especie = document.getElementById('especie').value;
                const edad = document.getElementById('edad').value;
                const tamano = document.getElementById('tamano').value;
                const genero = document.getElementById('genero').value;
                const busqueda = document.getElementById('busqueda').value;
                
                fetch(`filtrar_animales.php?especie=${especie}&edad=${edad}&tamano=${tamano}&genero=${genero}&busqueda=${busqueda}`)
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('.animales-grid').innerHTML = html;
                    });
            }
        });
    </script>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
    <?php
function listarAnimalesDisponiblesFiltrados($especie, $edad, $tamano, $genero, $busqueda) {
    global $conn;
    
    $animales = [];
    $sql = "SELECT * FROM animales WHERE estado = 'disponible'";
    $params = [];
    $types = '';
    
    if (!empty($especie)) {
        $sql .= " AND especie = ?";
        $params[] = $especie;
        $types .= 's';
    }
    
    if (!empty($edad)) {
        if ($edad == 'cachorro') {
            $sql .= " AND edad <= 1";
        } elseif ($edad == 'joven') {
            $sql .= " AND edad > 1 AND edad <= 5";
        } elseif ($edad == 'adulto') {
            $sql .= " AND edad > 5";
        }
    }
    
    if (!empty($tamano)) {
        $sql .= " AND tamano = ?";
        $params[] = $tamano;
        $types .= 's';
    }
    
    if (!empty($genero)) {
        $sql .= " AND genero = ?";
        $params[] = $genero;
        $types .= 's';
    }
    
    if (!empty($busqueda)) {
        $sql .= " AND (nombre LIKE ? OR raza LIKE ?)";
        $searchTerm = "%$busqueda%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    $sql .= " ORDER BY fecha_ingreso DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $animales[] = $row;
    }
    
    return $animales;
}
?>
</body>
</html>

<?php
require_once 'includes/footer.php'; ?>