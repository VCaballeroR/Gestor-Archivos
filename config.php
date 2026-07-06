<?php
/**
 * config.php
 * ----------------------------------------------------------------------
 * Punto único donde se configura la sesión y las cabeceras de seguridad
 * HTTP. Se incluye al inicio de cada archivo público (index, subir,
 * eliminar, descargar, seguridad) para que todos compartan exactamente
 * la misma configuración, sin repetirla.
 * ----------------------------------------------------------------------
 */

// Cookie de sesión endurecida:
// - httponly: inaccesible desde JavaScript (mitiga robo de sesión vía XSS)
// - samesite=Strict: no se envía en peticiones iniciadas desde otros sitios (mitiga CSRF)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Cabeceras de seguridad HTTP para todo el sitio
header('X-Frame-Options: DENY');                 // evita que el sitio se cargue dentro de un <iframe> (clickjacking)
header('X-Content-Type-Options: nosniff');        // evita que el navegador "adivine" un tipo MIME distinto
header('Referrer-Policy: strict-origin-when-cross-origin');

// Generar el token CSRF una sola vez por sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verifica el token CSRF recibido por POST contra el guardado en sesión.
 * Usa hash_equals() para comparar en tiempo constante y evitar timing attacks.
 */
function verificarCsrf(): bool
{
    $tokenEnviado = $_POST['csrf_token'] ?? '';
    $tokenSesion  = $_SESSION['csrf_token'] ?? '';
    return !empty($tokenSesion) && hash_equals($tokenSesion, $tokenEnviado);
}

/** Escapa texto para imprimirlo en HTML de forma segura (anti-XSS). */
function h(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
