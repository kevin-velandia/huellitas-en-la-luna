<?php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/funciones.php';
    
    $datos = [
        'nombre' => $_POST['nombre'],
        'email' => $_POST['email'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'telefono' => $_POST['telefono'],
        'direccion' => $_POST['direccion'],
        'rol' => 'voluntario'
    ];
    
    $sql = "INSERT INTO usuarios (nombre, email, password, telefono, direccion, rol) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", 
        $datos['nombre'],
        $datos['email'],
        $datos['password'],
        $datos['telefono'],
        $datos['direccion'],
        $datos['rol']
    );
    
    if ($stmt->execute()) {
        $exito = "Registro exitoso. Ahora puedes iniciar sesión.";
    } else {
        $error = "Error al registrar. Por favor intenta nuevamente.";
    }
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
        background-color: var(--light-color); /* Changed to beige claro */
    }
    
    .register-container {
        max-width: 600px;
        margin: 3rem auto;
        padding: 2rem;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .register-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 8px;
        background: linear-gradient(90deg, var(--primary-green), var(--secondary-orange));
    }
    
    h1 {
        text-align: center;
        margin-bottom: 1.5rem;
        color: var(--primary-green);
        font-size: 2.2rem;
        position: relative;
        padding-bottom: 1rem;
    }
    
    h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: var(--secondary-orange);
        border-radius: 2px;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--primary-green);
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--secondary-orange);
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 140, 66, 0.2);
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
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
    
    .btn:hover {
        background: #e67e36;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 140, 66, 0.3);
    }
    
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    
    .alert.success {
        background-color: #d4edda;
        color: var(--success-color);
        border-left: 5px solid var(--success-color);
    }
    
    .alert.error {
        background-color: #f8d7da;
        color: var(--error-color);
        border-left: 5px solid var(--error-color);
    }
    
    .register-container p {
        text-align: center;
        color: var(--dark-color);
    }
    
    .register-container p a {
        color: var(--primary-green);
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .register-container p a:hover {
        color: var(--secondary-orange);
        text-decoration: underline;
    }
    
    .volunteer-image {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .volunteer-image img {
        width: 120px;
        height: auto;
    }
    
    @media (max-width: 768px) {
        .register-container {
            margin: 2rem 1rem;
            padding: 1.5rem;
        }
        
        h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="register-container">
    <div class="volunteer-image">
        <img src="assets/img/voluntarios/voluntarios-14.jpg" alt="Ícono de voluntario">
    </div>
    
    <h1>Registro de Voluntario</h1>
    
    <?php if(isset($exito)): ?>
        <div class="alert success"><?= $exito ?></div>
    <?php elseif(isset($error)): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: María González" required>
        </div>
        
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" placeholder="Ej: ejemplo@mail.com" required>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
        </div>
        
        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" placeholder="Ej: 5551234567" required>
        </div>
        
        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <textarea id="direccion" name="direccion" placeholder="Ingresa tu dirección completa" required></textarea>
        </div>
        
        <button type="submit" class="btn">Unirse como Voluntario</button>
    </form>
    
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>