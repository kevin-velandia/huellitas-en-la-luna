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
require_once './vendor/autoload.php'; // carga la librería instalada por Composer
require_once 'config.php';           // carga tu token ($accessToken) u otras configuraciones

// -----------------------------------------------------------------

// 4. Obtener el monto del formulario de forma segura
$monto_donacion = (float)$_POST['monto'];

try {
    // 5. Configurar el SDK con tu Access Token
    // En la versión del SDK incluido aquí la clase de configuración es
    // MercadoPago\MercadoPagoConfig (no existe MercadoPago\SDK)
    MercadoPago\MercadoPagoConfig::setAccessToken($accessToken);


    // 6. Crear la preferencia de pago usando el cliente del SDK
    $preferenceClient = new MercadoPago\Client\Preference\PreferenceClient();

    // URLs de retorno necesarias cuando usas auto_return
    $backUrlBase = 'http://localhost/petlove';

    // Si estamos en localhost, Mercado Pago puede rechazar auto_return/back_urls en algunos casos.
    $isLocalhost = str_contains($backUrlBase, 'localhost') || str_contains($backUrlBase, '127.0.0.1');

    $request = [
        'items' => [
            [
                'title' => 'Donación para nuestra causa',
                'quantity' => 1,
                'unit_price' => $monto_donacion,
                'currency_id' => 'COP'
            ]
        ],
        'back_urls' => [
            'success' => $backUrlBase . '/confirmacion_donaciones.php',
            'failure' => $backUrlBase . '/donaciones.php',
            'pending' => $backUrlBase . '/confirmacion_donaciones.php'
        ]
    ];

    // Añadir auto_return sólo si no estamos en localhost (evita el error invalid_auto_return en dev)
    if (!$isLocalhost) {
        $request['auto_return'] = 'approved';
    }

    // Crear la preferencia en la API
    $preference = $preferenceClient->create($request);

    // Redirigir al usuario al checkout
    header("Location: " . $preference->init_point);
    exit();

} catch (MercadoPago\Exceptions\MPApiException $e) {
    // Mostrar detalles de la respuesta de la API para depurar
    $status = $e->getStatusCode();
    $apiResponse = $e->getApiResponse()->getContent();
    echo "Error al procesar el pago: Api error (HTTP $status). Detalles: " . json_encode($apiResponse);
} catch (Exception $e) {
    echo 'Error al procesar el pago: ' . $e->getMessage();
}

?>