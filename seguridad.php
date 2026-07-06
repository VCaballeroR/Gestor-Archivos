<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/GestorArchivos.php';

// Reutilizamos la clase para mostrar estadísticas reales de /uploads
$gestor   = new GestorArchivos(__DIR__ . '/uploads');
$archivos = $gestor->listar();

$conteoPorTipo = ['pdf' => 0, 'jpg' => 0, 'png' => 0];
foreach ($archivos as $f) {
    $ext = strtolower(pathinfo($f['nombre'], PATHINFO_EXTENSION));
    $key = ($ext === 'jpeg') ? 'jpg' : $ext;
    if (isset($conteoPorTipo[$key])) {
        $conteoPorTipo[$key]++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad — Gestor de Archivos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <h1>🛡️ Medidas de Seguridad</h1>
    <p>Referencia técnica de cada capa de protección implementada en el sistema</p>
</header>

<nav>
    <a href="index.php">Inicio</a>
    <a href="seguridad.php" class="activo">Seguridad</a>
</nav>

<main>

    <section aria-labelledby="h-stats">
        <h2 id="h-stats">Estado actual del sistema</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <strong><?= $conteoPorTipo['pdf'] ?></strong>
                <span>PDF</span>
            </div>
            <div class="stat-item">
                <strong><?= $conteoPorTipo['jpg'] ?></strong>
                <span>JPG</span>
            </div>
            <div class="stat-item">
                <strong><?= $conteoPorTipo['png'] ?></strong>
                <span>PNG</span>
            </div>
            <div class="stat-item">
                <strong><?= count($archivos) ?></strong>
                <span>Total</span>
            </div>
        </div>
    </section>

    <section aria-labelledby="h-subida">
        <h2 id="h-subida">Al subir un archivo</h2>
        <ul class="lista-seguridad">
            <li>
                <strong>Tipo MIME real con <code>finfo</code>:</strong>
                se detecta el tipo real leyendo el contenido del archivo, en vez de confiar
                en la cabecera que envía el navegador (fácilmente falsificable).
            </li>
            <li>
                <strong>Lista blanca de extensiones:</strong>
                solo se aceptan PDF, JPG y PNG; cualquier otra extensión se rechaza antes
                de tocar el disco.
            </li>
            <li>
                <strong>Coherencia MIME ↔ extensión:</strong>
                el tipo detectado y la extensión declarada deben coincidir, lo que evita
                archivos "políglotos" (por ejemplo, un script disfrazado de imagen).
            </li>
            <li>
                <strong>Límite de tamaño:</strong>
                los archivos mayores a 2 MB se rechazan antes de procesarse.
            </li>
            <li>
                <strong>Renombrado con SHA-256:</strong>
                el archivo se guarda con un hash de su contenido más un timestamp, nunca
                con el nombre original. Esto hace imposible adivinar el nombre de otro
                archivo y elimina cualquier riesgo de ejecución vía nombre.
            </li>
            <li>
                <strong><code>.htaccess</code> en <code>/uploads</code>:</strong>
                bloquea la ejecución de scripts dentro de la carpeta de almacenamiento,
                incluso si alguien lograra subir uno.
            </li>
        </ul>
    </section>

    <section aria-labelledby="h-gestion">
        <h2 id="h-gestion">Al listar, descargar y eliminar</h2>
        <ul class="lista-seguridad">
            <li>
                <strong>Prevención de Path Traversal:</strong>
                <code>basename()</code> elimina cualquier <code>../</code> del nombre recibido, una
                expresión regular exige que coincida exactamente con el patrón de nombre
                seguro generado al subir, y <code>realpath()</code> confirma que la ruta final
                sigue dentro de <code>/uploads</code>.
            </li>
            <li>
                <strong>Descarga forzada:</strong>
                se sirve con <code>Content-Type: application/octet-stream</code>, así el
                navegador nunca intenta ejecutar el archivo, solo lo descarga.
            </li>
            <li>
                <strong>Confirmación antes de eliminar:</strong>
                un cuadro de diálogo pide confirmar la acción antes de enviar el formulario.
            </li>
        </ul>
    </section>

    <section aria-labelledby="h-general">
        <h2 id="h-general">Protección general del sitio</h2>
        <ul class="lista-seguridad">
            <li>
                <strong>Token CSRF:</strong>
                cada formulario incluye un token único de sesión generado con
                <code>random_bytes(32)</code>, verificado con <code>hash_equals()</code> antes de
                aceptar cualquier subida o eliminación.
            </li>
            <li>
                <strong>Anti-XSS:</strong>
                todo nombre de archivo o mensaje que se imprime en el HTML pasa por
                <code>htmlspecialchars()</code>.
            </li>
            <li>
                <strong>Cookies de sesión endurecidas:</strong>
                <code>HttpOnly</code> (inaccesibles desde JavaScript) y
                <code>SameSite=Strict</code> (no se envían desde otros sitios).
            </li>
            <li>
                <strong>Cabeceras HTTP:</strong>
                <code>X-Frame-Options: DENY</code> evita clickjacking y
                <code>X-Content-Type-Options: nosniff</code> evita que el navegador reinterprete
                el tipo de un archivo.
            </li>
        </ul>
    </section>

</main>

<footer>
    <p>Valerie Caballero — Módulo POO de gestión segura de archivos en PHP</p>
</footer>

</body>
</html>
