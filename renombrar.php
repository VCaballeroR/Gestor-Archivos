<?php
/**
 * Procesador de renombrado. Igual que eliminar.php, delega toda la validación
 * de seguridad (anti path-traversal, saneamiento del nuevo nombre) a
 * GestorArchivos::renombrar(). Nunca toca el nombre físico del archivo en
 * disco: solo actualiza la etiqueta "visible" que se muestra en pantalla.
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

if (!isset($_POST['archivo'], $_POST['nuevo_nombre'])) {
    $_SESSION['msg_err'] = 'Solicitud inválida.';
    header('Location: index.php');
    exit;
}

$gestor    = new GestorArchivos(__DIR__ . '/uploads');
$resultado = $gestor->renombrar($_POST['archivo'], $_POST['nuevo_nombre']);

$_SESSION[$resultado['exito'] ? 'msg_ok' : 'msg_err'] = $resultado['mensaje'];

header('Location: index.php');
exit;
