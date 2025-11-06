<?php
// Evitar acceso directo al archivo
if (!defined('PHP_VERSION')) {
    exit('Acceso denegado');
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'petlove_foundation');

// Configuración del sitio
define('SITE_NAME', 'PetLove Fundación');
define('SITE_URL', 'http://localhost/petlove');

// Rutas importantes (opcional)
define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');

 
?>


