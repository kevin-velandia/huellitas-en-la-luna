<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

$especie = $_GET['especie'] ?? '';
$edad = $_GET['edad'] ?? '';
$tamano = $_GET['tamano'] ?? '';
$genero = $_GET['genero'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT * FROM animales WHERE estado = 'disponible'";
$params = [];

if (!empty($especie)) {
    $sql .= " AND especie = ?";
    $params[] = $especie;
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
}

if (!empty($genero)) {
    $sql .= " AND genero = ?";
    $params[] = $genero;
}

if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR raza LIKE ?)";
    $searchTerm = "%$busqueda%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$animales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($animales as $animal): ?>
<article class="animal-card">
    <img src="<?= htmlspecialchars($animal['foto']) ?>" alt="<?= htmlspecialchars($animal['nombre']) ?>">
    <div class="animal-info">
        <h3><?= htmlspecialchars($animal['nombre']) ?></h3>
        <p><strong>Especie:</strong> <?= htmlspecialchars($animal['especie']) ?></p>
        <p><strong>Edad:</strong> <?= htmlspecialchars($animal['edad']) ?> años</p>
        <p><strong>Raza:</strong> <?= htmlspecialchars($animal['raza']) ?></p>
        <p><strong>Tamaño:</strong> <?= htmlspecialchars($animal['tamano']) ?></p>
        <p><strong>Género:</strong> <?= htmlspecialchars($animal['genero']) ?></p>
        <p><?= htmlspecialchars($animal['descripcion']) ?></p>
        
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
</article>
<?php endforeach;