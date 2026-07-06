<?php
/**
 * Procesador de subida. No contiene lógica de validación de archivos:
 * toda esa responsabilidad vive dentro de GestorArchivos. Aquí solo se
 * verifica que la petición sea legítima (POST + CSRF) y se delega el
 * trabajo real al objeto. Patrón PRG (Post/Redirect/Get): siempre
 * redirige a index.php con un mensaje flash en sesión.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/GestorArchivos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verificarCsrf()) {
    $_SESSION['msg_err'] = 'Token de seguridad inválido. Recarga la página e inténtalo de nuevo.';
    header('Location: index.php');
    exit;
}

if (!isset($_FILES['archivo'])) {
    $_SESSION['msg_err'] = 'No se recibió ningún archivo.';
    header('Location: index.php');
    exit;
}

$gestor    = new GestorArchivos(__DIR__ . '/uploads');
$resultado = $gestor->subir($_FILES['archivo']);

$_SESSION[$resultado['exito'] ? 'msg_ok' : 'msg_err'] = $resultado['mensaje'];

header('Location: index.php');
exit;
