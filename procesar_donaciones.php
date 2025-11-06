<?php
// procesar_donaciones.php

// 1. Validar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Acceso no permitido.";
    exit;
}

// 2. Validar que el monto fue enviado y es un número válido
if (!isset($_POST['monto']) || !is_numeric($_POST['monto']) || $_POST['monto'] <= 0) {
    echo "Por favor, ingresa un monto válido.";
    exit;
}

// -----------------------------------------------------------------
// ¡ESTA ES LA PARTE CRÍTICA!
// Asegúrate de que estas dos líneas estén aquí, al principio.
// -----------------------------------------------------------------

// 3. Incluir el SDK de MercadoPago y la configuración
require_once './vendor/autoload.php'; // <-- ESTA LÍNEA CARGA LA LIBRERÍA
require_once 'config.php';           // <-- ESTA LÍNEA CARGA TU TOKEN

// -----------------------------------------------------------------

// 4. Obtener el monto del formulario de forma segura
$monto_donacion = (float)$_POST['monto'];

try {
    // 5. Configurar el SDK con tu Access Token
    // ESTA LÍNEA (la 25 en tu error) AHORA FUNCIONARÁ PORQUE LA CLASE YA FUE CARGADA
    MercadoPago\SDK::setAccessToken($accessToken);
    

    // 6. Crear la preferencia de pago
    $preference = new MercadoPago\Preference();

    // ... el resto del código para crear el item y la preferencia ...
    $item = new MercadoPago\Item();
    $item->title = 'Donación para nuestra causa';
    $item->quantity = 1;
    $item->unit_price = $monto_donacion;
    $item->currency_id = "COP"; // Cambia a tu moneda

    $preference->items = array($item);

  
    $preference->auto_return = "approved";

    $preference->save();

    header("Location: " . $preference->init_point);
    exit();

} catch (Exception $e) {
    echo 'Error al procesar el pago: ' . $e->getMessage();
}

?>