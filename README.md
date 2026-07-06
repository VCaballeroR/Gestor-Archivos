# Gestor de Archivos Seguro — Módulo Web en PHP (POO)

## 1. Descripción del sistema

Módulo web que permite **subir, visualizar y eliminar archivos** (PDF, JPG, PNG)
desde el navegador, implementado en PHP con **Programación Orientada a Objetos**.
Toda la lógica de manejo de archivos está centralizada en la clase `GestorArchivos`,
que aplica validaciones estrictas para prevenir los ataques más comunes asociados a
la subida de archivos: ejecución remota de código, path traversal, archivos
disfrazados (políglotos) y CSRF.

## 2. Instrucciones de uso

1. Copiar la carpeta `GestorArchivos/` dentro de `htdocs` (XAMPP).
2. Iniciar Apache desde el panel de control de XAMPP.
3. Abrir en el navegador: `http://localhost/GestorArchivos/index.php`
4. Desde el formulario, seleccionar un archivo PDF, JPG o PNG (máx. 2 MB) y
   presionar **Subir archivo**.
5. El archivo aparece como una tarjeta en "Archivos subidos", donde se puede:
   - **Descargar** el archivo.
   - **Renombrar** el archivo (solo cambia el nombre que se muestra en pantalla).
   - **Eliminar** el archivo (se pide confirmación antes de borrarlo).
6. La pestaña **Seguridad** muestra en detalle cada medida aplicada, con
   estadísticas en tiempo real de los archivos almacenados.

No se requiere base de datos: el listado se genera leyendo directamente la
carpeta `/uploads`. Los nombres "visibles" (el que ve el usuario, y el que
puede editar con **Renombrar**) se guardan aparte, en `/data/nombres.json`,
un archivo protegido con su propio `.htaccess` que bloquea cualquier acceso
directo por HTTP. El nombre físico real del archivo en disco (el hash
SHA-256 + timestamp) nunca cambia: renombrar es puramente una etiqueta de
presentación y no debilita ninguna de las protecciones contra path
traversal o adivinación de nombres.

## 3. Explicación de la clase utilizada

`clases/GestorArchivos.php` encapsula toda la lógica de negocio:

```php
class GestorArchivos {
    private string $directorio;
    private int    $tamanoMaximo;

    public function __construct(string $directorio, int $tamanoMaximoMB = 2)
    public function subir(array $archivo): array
    public function listar(): array
    public function eliminar(string $nombre): array
    public function renombrar(string $nombreArchivo, string $nuevoNombreBase): array
    public function descargar(string $nombre): void
}
```

- **Encapsulamiento**: `$directorio` y `$tamanoMaximo` son `private`; ningún
  script externo puede alterarlos, solo se accede mediante los métodos públicos.
- **Abstracción**: `index.php`, `subir.php` y `eliminar.php` solo llaman a
  `->subir()`, `->listar()` o `->eliminar()`; no conocen (ni necesitan conocer)
  cómo se valida un MIME o cómo se genera el nombre seguro internamente.
- **Responsabilidad única**: la clase gestiona un solo directorio de archivos;
  por eso se reutiliza igual en `index.php`, `seguridad.php` y cada procesador.
- **Métodos privados de apoyo**: `crearHtaccess()`, `resolverRutaSegura()`,
  `formatearTamano()`, `mensajeErrorSubida()` y `respuesta()` son detalles de
  implementación que solo usan los métodos públicos internamente.

El constructor se "auto-protege": crea la carpeta `/uploads` si no existe y
genera su propio `.htaccess` de bloqueo, sin que quien instancia el objeto
tenga que acordarse de hacerlo.

## 4. Medidas de seguridad aplicadas

- **Subida de scripts maliciosos** (`.php`, `.exe`, etc.)
  Validación de extensión **y** de tipo MIME real con `finfo` (nunca se confía en `$_FILES['type']`, falsificable desde el navegador).

- **Archivos "políglotos"** (`foto.php.jpg`)
  Se exige coherencia exacta entre el MIME real y la extensión declarada.

- **Ejecución de archivos subidos**
  `.htaccess` autogenerado en `/uploads` deniega la ejecución de scripts y desactiva el motor PHP en esa carpeta; permisos `0644` (no ejecutable).

- **Adivinar o colisionar nombres de archivo**
  Renombrado con **hash SHA-256 del contenido + timestamp**; el nombre original nunca se conserva en el disco.

- **Archivos demasiado grandes** (DoS)
  Límite de 2 MB validado en el servidor antes de procesar el archivo.

- **Path traversal al eliminar/descargar** (`../../etc/passwd`)
  `basename()` + expresión regular estricta (el nombre debe coincidir con el patrón `hash_timestamp.ext`) + `realpath()` para confirmar que la ruta final sigue dentro de `/uploads`.

- **CSRF** (subir/eliminar/renombrar archivos en nombre de otro usuario)
  Token único por sesión (`random_bytes(32)`), verificado con `hash_equals()` en cada formulario.

- **XSS al mostrar nombres o mensajes**
  Todo se imprime con `htmlspecialchars()`.

- **Robo de sesión / clickjacking**
  Cookies `HttpOnly` + `SameSite=Strict`; cabeceras `X-Frame-Options: DENY` y `X-Content-Type-Options: nosniff`.

- **Listado de directorio expuesto**
  `Options -Indexes` en `.htaccess` + `index.php` de bloqueo dentro de `/uploads`.

- **Eliminación accidental**
  Confirmación con `confirm()` en JavaScript antes de enviar el formulario.

- **Subida falsificada fuera del formulario**
  Verificación con `is_uploaded_file()` antes de mover cualquier archivo.

## 5. Estructura del proyecto

```
GestorArchivos/
├── index.php              # Formulario de subida + listado en tarjetas
├── subir.php                # Procesa la subida (verifica CSRF)
├── eliminar.php              # Procesa la eliminación (verifica CSRF)
├── renombrar.php              # Procesa el cambio de nombre visible (verifica CSRF)
├── descargar.php              # Sirve el archivo para descarga segura
├── seguridad.php               # Página de referencia técnica de seguridad
├── config.php                   # Sesión endurecida, cabeceras HTTP y helpers (CSRF, h())
├── clases/
│   └── GestorArchivos.php        # Clase POO principal
├── css/
│   └── style.css                  # Estilos (tema oscuro, tarjetas)
├── uploads/                        # Carpeta protegida donde se guardan los archivos
│   ├── .htaccess                    # Bloquea ejecución y listado (autogenerado también en runtime)
│   └── index.php                     # Bloquea acceso directo
├── data/                             # Metadatos: mapa nombre físico -> nombre visible
│   ├── .htaccess                      # Bloquea todo acceso HTTP (autogenerado en runtime)
│   └── nombres.json                    # Se crea automáticamente al subir/renombrar el primer archivo
└── README.md
```
