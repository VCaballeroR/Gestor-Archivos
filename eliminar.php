<?php
/**
 * Procesador de eliminación. Igual que subir.php, delega toda la
 * validación de seguridad (anti path-traversal) a GestorArchivos::eliminar().
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

if (!isset($_POST['archivo'])) {
    $_SESSION['msg_err'] = 'Solicitud inválida.';
    header('Location: index.php');
    exit;
}

$gestor    = new GestorArchivos(__DIR__ . '/uploads');
$resultado = $gestor->eliminar($_POST['archivo']);

$_SESSION[$resultado['exito'] ? 'msg_ok' : 'msg_err'] = $resultado['mensaje'];

header('Location: index.php');
exit;
