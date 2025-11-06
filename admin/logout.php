<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario']['email'] ?? 'Desconocido';
    error_log("[" . date('Y-m-d H:i:s') . "] Cierre de sesión - Usuario: " . $usuario);
}


$_SESSION = [];


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


session_destroy();


header("Location: ../login.php?logout=success");
exit();
?>