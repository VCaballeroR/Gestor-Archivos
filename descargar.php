<?php
/**
 * Sirve un archivo para descarga forzada. No requiere token CSRF porque
 * es una operación de solo lectura (GET) sin efectos secundarios; el
 * nombre recibido de todas formas se valida estrictamente dentro de
 * GestorArchivos::descargar() antes de tocar el disco.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/GestorArchivos.php';

$gestor = new GestorArchivos(__DIR__ . '/uploads');
$gestor->descargar($_GET['archivo'] ?? '');
