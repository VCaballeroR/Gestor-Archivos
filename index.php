<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/GestorArchivos.php';

$gestor   = new GestorArchivos(__DIR__ . '/uploads');
$archivos = $gestor->listar();

// Mensajes flash guardados en sesión por subir.php / eliminar.php / renombrar.php (patrón PRG)
$mensaje = $_SESSION['msg_ok'] ?? $_SESSION['msg_err'] ?? '';
$tipo    = isset($_SESSION['msg_ok']) ? 'exito' : (isset($_SESSION['msg_err']) ? 'error' : '');
unset($_SESSION['msg_ok'], $_SESSION['msg_err']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Archivos Seguro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <h1>📁 Gestor de Archivos Seguro</h1>
    <p>Módulo de subida, visualización y eliminación de archivos en PHP con POO</p>
</header>

<nav>
    <a href="index.php" class="activo">Inicio</a>
    <a href="seguridad.php">Seguridad</a>
</nav>

<main>

    <?php if ($mensaje): ?>
        <div class="alerta <?= $tipo === 'exito' ? 'alerta-exito' : 'alerta-error' ?>">
            <?= h($mensaje) ?>
        </div>
    <?php endif; ?>

    <section id="subir" aria-labelledby="h-subir">
        <h2 id="h-subir">Subir nuevo archivo</h2>
        <form action="subir.php" method="POST" enctype="multipart/form-data" id="form-subir">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">

            <label for="archivo" class="dropzone" id="dropzone">
                <span class="dropzone-icono">⬆️</span>
                <span class="dropzone-texto" id="dropzone-texto">Arrastra un archivo aquí o haz clic para elegirlo</span>
                <span class="dropzone-ayuda">PDF, JPG o PNG — máx. 2 MB</span>
            </label>
            <input type="file" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required>

            <button type="submit">Subir archivo</button>
        </form>
    </section>

    <section id="listado" aria-labelledby="h-listado">
        <h2 id="h-listado">Archivos subidos</h2>

        <?php if (empty($archivos)): ?>
            <p class="vacio">Aún no hay archivos subidos.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tamaño</th>
                        <th>Fecha de subida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivos as $i => $archivo):
                        $nombreBaseActual = pathinfo($archivo['nombre_visible'], PATHINFO_FILENAME);
                    ?>
                        <tr>
                            <td class="nombre-archivo">
                                <div class="nombre-visible" title="<?= h($archivo['nombre_visible']) ?>">
                                    <?= h($archivo['nombre_visible']) ?>
                                </div>
                                <div class="nombre-fisico" title="<?= h($archivo['nombre']) ?>">
                                    💾 <?= h($archivo['nombre']) ?>
                                </div>
                            </td>
                            <td><?= h($archivo['tamano']) ?></td>
                            <td><?= h($archivo['fecha']) ?></td>
                            <td class="acciones">
                                <a class="btn btn-descargar"
                                   href="descargar.php?archivo=<?= urlencode($archivo['nombre']) ?>">
                                   Descargar
                                </a>

                                <button type="button" class="btn btn-renombrar" data-toggle-renombrar="rn-<?= $i ?>">
                                    ✏️ Renombrar
                                </button>

                                <form action="eliminar.php" method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar este archivo? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="archivo" value="<?= h($archivo['nombre']) ?>">
                                    <button type="submit" class="btn btn-eliminar">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="fila-renombrar oculto" id="rn-<?= $i ?>">
                            <td colspan="4">
                                <form action="renombrar.php" method="POST" class="form-renombrar">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="archivo" value="<?= h($archivo['nombre']) ?>">
                                    <label for="nuevo-nombre-<?= $i ?>">Nuevo nombre para <strong><?= h($archivo['nombre_visible']) ?></strong>:</label>
                                    <div class="form-renombrar-linea">
                                        <input type="text" id="nuevo-nombre-<?= $i ?>" name="nuevo_nombre"
                                               value="<?= h($nombreBaseActual) ?>" maxlength="120" required autocomplete="off">
                                        <button type="submit" class="btn btn-guardar">Guardar</button>
                                        <button type="button" class="btn btn-cancelar" data-toggle-renombrar="rn-<?= $i ?>">Cancelar</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</main>

<footer>
    <p>Valerie Caballero — Módulo POO de gestión segura de archivos en PHP</p>
</footer>

<script>
    // Alterna la visibilidad del formulario de renombrar de una tarjeta
    document.querySelectorAll('[data-toggle-renombrar]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var formulario = document.getElementById(boton.dataset.toggleRenombrar);
            formulario.classList.toggle('oculto');
            if (!formulario.classList.contains('oculto')) {
                var input = formulario.querySelector('input[type="text"]');
                input.focus();
                input.select();
            }
        });
    });

    // Mejora visual del input de archivo: nombre elegido + arrastrar y soltar
    var inputArchivo   = document.getElementById('archivo');
    var dropzone        = document.getElementById('dropzone');
    var dropzoneTexto  = document.getElementById('dropzone-texto');

    if (inputArchivo && dropzone) {
        inputArchivo.addEventListener('change', function () {
            if (inputArchivo.files.length > 0) {
                dropzoneTexto.textContent = inputArchivo.files[0].name;
                dropzone.classList.add('dropzone-activa');
            }
        });

        ['dragenter', 'dragover'].forEach(function (evento) {
            dropzone.addEventListener(evento, function (e) {
                e.preventDefault();
                dropzone.classList.add('dropzone-hover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evento) {
            dropzone.addEventListener(evento, function (e) {
                e.preventDefault();
                dropzone.classList.remove('dropzone-hover');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length > 0) {
                inputArchivo.files = e.dataTransfer.files;
                dropzoneTexto.textContent = e.dataTransfer.files[0].name;
                dropzone.classList.add('dropzone-activa');
            }
        });
    }
</script>

</body>
</html>
