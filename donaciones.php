<?php
require_once 'includes/header.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Debes iniciar sesión para realizar donaciones";
    header("Location: login.php");
    exit();
}

// Mostrar mensajes de éxito/error
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['exito'])) {
    echo '<div class="alert alert-success">' . $_SESSION['exito'] . '</div>';
    unset($_SESSION['exito']);
}
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
    
    .donation-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    h1.donation-title {
        text-align: center;
        margin: 2rem 0;
        color: var(--primary-color);
        font-size: 2.5rem;
        position: relative;
        padding-bottom: 1rem;
    }
    
    h1.donation-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: var(--secondary-color);
        border-radius: 2px;
    }
    
    .donation-options {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .donation-option {
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        width: 100%;
        max-width: 500px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        height: fit-content;
    }
    
    .donation-option:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    
    .donation-option h2 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        text-align: center;
        font-size: 1.8rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.2);
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .btn-donate {
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
        margin-top: auto;
    }
    
    .btn-donate:hover {
        background: #26784d;
        transform: translateY(-2px);
    }
    
    .donation-hero {
        background: linear-gradient(rgba(46, 139, 87, 0.8), rgba(255, 140, 66, 0.8)), url('assets/img/donaciones/donation-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        padding: 4rem 1rem;
    }
    
    .donation-hero h1 {
        color: white;
        font-size: 2.8rem;
        margin-bottom: 1rem;
    }
    
    .donation-hero p {
        max-width: 800px;
        margin: 0 auto;
        font-size: 1.2rem;
    }
    
    .donation-icon {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .donation-icon img {
        width: 80px;
        height: auto;
        border-radius: 10px;
    }
    
    .file-upload {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .file-upload label {
        font-weight: 600;
    }
    
    .file-upload input[type="file"] {
        padding: 0.5rem;
    }

    /* Nuevos estilos para nivelar las secciones */
    .donation-options-leveled {
        display: flex;
        justify-content: center;
        align-items: stretch;
        gap: 2rem;
        margin: 3rem 0;
    }
    
    .donation-card {
        flex: 1;
        max-width: 500px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        padding: 2.5rem;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    .donation-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .card-header h2 {
        color: var(--primary-color);
        font-size: 1.8rem;
        margin: 1rem 0 0.5rem 0;
    }
    
    .card-header .donation-description {
        color: var(--dark-color);
        font-size: 0.9rem;
        opacity: 0.8;
    }
    
    .donation-form {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .form-content {
        flex: 1;
    }
    
    @media (max-width: 768px) {
        .donation-options-leveled {
            flex-direction: column;
            align-items: center;
        }
        
        .donation-card {
            width: 90%;
            max-width: 100%;
        }
        
        h1.donation-title {
            font-size: 2rem;
        }
        
        .donation-hero h1 {
            font-size: 2.2rem;
        }
    }
</style>

<div class="donation-hero">
    <h1>Apoya a Huellitas en la luna</h1>
    <p>Tu contribución nos ayuda a seguir cuidando y protegiendo a los animales que más lo necesitan</p>
</div>

<div class="donation-container">
    <h1 class="donation-title">Realiza tu Donación</h1>
    
    <div class="donation-options-leveled">
        <!-- Donación Monetaria -->
        <div class="donation-card">
            <div class="card-header">
                <div class="donation-icon">
                    <img src="assets/img/donaciones/donaciones-1.jpg" alt="Donación monetaria">
                </div>
                <h2>Donación Monetaria</h2>
                <p class="donation-description">Contribuye con apoyo económico directo</p>
            </div>
            <form action="procesar_donaciones.php" method="post" class="donation-form">
                <input type="hidden" name="tipo" value="monetaria">
                <div class="form-content">
                    <div class="form-group">
                        <label for="monto">Monto ($):</label>
                        <input type="number" id="monto" name="monto" min="1" step="0.01" placeholder="Ej: 50.00" required>
                    </div>
                </div>
                <button type="submit" class="btn-donate">Donar ahora</button>
            </form>
        </div>

        <!-- Donación en Especie -->
        <div class="donation-card">
            <div class="card-header">
                <div class="donation-icon">
                    <img src="assets/img/donaciones/donaciones-2.jpg" alt="Donación en especie">
                </div>
                <h2>Donación en Especie</h2>
                <p class="donation-description">Contribuye con alimentos y suministros</p>
            </div>
            <form action="procesar_donaciones.php" method="post" enctype="multipart/form-data" class="donation-form">
                <input type="hidden" name="tipo" value="especie">
                <div class="form-content">
                    <div class="form-group">
                        <label for="tipo_especie">Tipo de donación:</label>
                        <select id="tipo_especie" name="tipo_especie" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="comida">Comida para animales</option>
                            <option value="medicamentos">Medicamentos</option>
                            <option value="accesorios">Accesorios</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" placeholder="Describa los artículos que desea donar" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" placeholder="Ej: 5" required>
                    </div>
                    <div class="form-group">
                        <label for="unidad">Unidad de medida:</label>
                        <input type="text" id="unidad" name="unidad" placeholder="Ej: kg, unidades, litros" value="unidades">
                    </div>
                    <div class="form-group file-upload">
                        <label for="comprobante">Comprobante (opcional):</label>
                        <input type="file" id="comprobante" name="comprobante" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Formatos aceptados: JPG, PNG, PDF (max 2MB)</small>
                    </div>
                </div>
                <button type="submit" class="btn-donate">Enviar donación</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>