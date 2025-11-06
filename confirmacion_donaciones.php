<?php
require_once 'includes/header.php';

// Verificar si hay mensaje de éxito
if (!isset($_SESSION['exito'])) {
    header("Location: donaciones.php");
    exit();
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-success">
                <div class="card-header bg-success text-white">
                    <h2 class="mb-0 text-center"><i class="bi bi-check-circle"></i> Donación Registrada</h2>
                </div>
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-3">¡Gracias por tu apoyo!</h3>
                    <p class="lead"><?= $_SESSION['exito'] ?></p>
                    <p>Hemos registrado tu donación y pronto nos pondremos en contacto contigo.</p>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="mis_donaciones.php" class="btn btn-primary">
                            <i class="bi bi-list-check"></i> Ver mis donaciones
                        </a>
                        <a href="donaciones.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-repeat"></i> Hacer otra donación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Limpiar mensaje de sesión
unset($_SESSION['exito']);
require_once 'includes/footer.php';
?>