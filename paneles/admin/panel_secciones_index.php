<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

require_once '../../conexion/db.php';

$mensaje = "";
$claseMensaje = "mensaje-admin";
$carpetaImagenes = '../../img/';

// Información institucional disponible para enlazar desde las imágenes del carrusel (id => título)
$informaciones = [];
$resultadoInformaciones = $conexion->query("SELECT id, titulo FROM informacion_institucional ORDER BY fecha_publicacion DESC");
while ($fila = $resultadoInformaciones->fetch_assoc()) {
    $informaciones[(int) $fila['id']] = $fila['titulo'];
}

// Devuelve el id si corresponde a una información existente; si no, null (sin enlace)
function validarInformacion($valor, $informaciones) {
    $id = (int) $valor;
    return isset($informaciones[$id]) ? $id : null;
}

// Si el servidor rechaza la subida por tamaño total, PHP deja $_POST y $_FILES vacíos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $mensaje = "Las imágenes superan el tamaño total que permite el servidor. Sube menos imágenes a la vez.";
    $claseMensaje = "mensaje-admin mensaje-error";
}

// Editar título y descripción de las tarjetas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id          = $_POST['id'];
    $titulo      = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];

    $stmt = $conexion->prepare("UPDATE secciones_index SET titulo = ?, descripcion = ? WHERE id = ?");
    $stmt->bind_param("ssi", $titulo, $descripcion, $id);
    $stmt->execute();
    $stmt->close();

    $mensaje = "Sección actualizada correctamente.";
}

// Agregar una o varias imágenes al carrusel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrusel'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $tamanoMaximo = 5 * 1024 * 1024; // 5 MB por imagen
    $subidas = 0;
    $rechazadas = 0;

    if (isset($_FILES['imagenes_carrusel'])) {
        $archivos = $_FILES['imagenes_carrusel'];
        $cantidad = count($archivos['name']);
        $idInformacion = validarInformacion($_POST['id_informacion'] ?? 0, $informaciones);
        $stmt = $conexion->prepare("INSERT INTO carrusel_imagenes (imagen, id_informacion) VALUES (?, ?)");

        for ($i = 0; $i < $cantidad; $i++) {
            if ($archivos['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($archivos['error'][$i] !== UPLOAD_ERR_OK || $archivos['size'][$i] > $tamanoMaximo) {
                $rechazadas++;
                continue;
            }

            $extension = strtolower(pathinfo($archivos['name'][$i], PATHINFO_EXTENSION));
            $esImagenValida = in_array($extension, $extensionesPermitidas)
                && @getimagesize($archivos['tmp_name'][$i]) !== false;

            if (!$esImagenValida) {
                $rechazadas++;
                continue;
            }

            $nombreArchivo = 'Carrusel_' . time() . '_' . $i . '_' . bin2hex(random_bytes(3)) . '.' . $extension;

            if (move_uploaded_file($archivos['tmp_name'][$i], $carpetaImagenes . $nombreArchivo)) {
                $rutaImagen = 'img/' . $nombreArchivo;
                $stmt->bind_param("si", $rutaImagen, $idInformacion);
                $stmt->execute();
                $subidas++;
            } else {
                $rechazadas++;
            }
        }
        $stmt->close();
    }

    if ($subidas > 0 && $rechazadas === 0) {
        $mensaje = $subidas === 1 ? "Imagen agregada al carrusel." : "$subidas imágenes agregadas al carrusel.";
    } elseif ($subidas > 0) {
        $mensaje = "Se agregaron $subidas imagen(es). $rechazadas no se pudieron subir (usa jpg, jpeg, png o webp de máximo 5 MB).";
    } else {
        $mensaje = "No se agregó ninguna imagen. Usa archivos jpg, jpeg, png o webp de máximo 5 MB.";
        $claseMensaje = "mensaje-admin mensaje-error";
    }
}

// Cambiar a qué información lleva una imagen del carrusel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_enlace'])) {
    $idImagen = (int) $_POST['id_imagen'];
    $idInformacion = validarInformacion($_POST['id_informacion'] ?? 0, $informaciones);

    $stmt = $conexion->prepare("UPDATE carrusel_imagenes SET id_informacion = ? WHERE id = ?");
    $stmt->bind_param("ii", $idInformacion, $idImagen);
    $stmt->execute();
    $stmt->close();

    $mensaje = $idInformacion === null
        ? "La imagen quedó sin enlace."
        : "Enlace de la imagen actualizado.";
}

// Eliminar una imagen del carrusel (de la base de datos y de la carpeta img)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_carrusel'])) {
    $idImagen = (int) $_POST['id_imagen'];

    $stmt = $conexion->prepare("SELECT imagen FROM carrusel_imagenes WHERE id = ?");
    $stmt->bind_param("i", $idImagen);
    $stmt->execute();
    $imagenEliminar = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($imagenEliminar) {
        $archivoReal = realpath('../../' . $imagenEliminar['imagen']);
        $carpetaReal = realpath($carpetaImagenes);

        // Solo borra el archivo si realmente está dentro de la carpeta img
        if ($archivoReal !== false && $carpetaReal !== false
            && strpos($archivoReal, $carpetaReal) === 0 && is_file($archivoReal)) {
            unlink($archivoReal);
        }

        $stmt = $conexion->prepare("DELETE FROM carrusel_imagenes WHERE id = ?");
        $stmt->bind_param("i", $idImagen);
        $stmt->execute();
        $stmt->close();

        $mensaje = "Imagen eliminada del carrusel.";
    }
}

$resultadoCarrusel = $conexion->query("SELECT * FROM carrusel_imagenes ORDER BY id ASC");

// Las tarjetas (título/descripción) se listan sin la fila antigua de la imagen principal
$resultado = $conexion->query("SELECT * FROM secciones_index WHERE clave != 'imagen_principal' ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Secciones del Inicio</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_admin.php'; ?>

<main class="container-fluid">
    <div class="encabezado-panel">
        <h2>Panel Administrador — Secciones del Inicio</h2>
    </div>
    <div class="explanation-table">
        <p class="text-explanation">
            Desde aquí puedes administrar las imágenes del carrusel del index, y el título
            y la descripción de las 3 tarjetas que aparecen en la página principal del sitio.
        </p>
    </div>

    <?php if ($mensaje): ?>
        <p class="<?= $claseMensaje ?>" role="alert"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <div class="seccion-admin-card">
        <div class="seccion-admin-header">
            <h3>🖼️ Carrusel de imágenes del index</h3>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="campo-editar">
                <label class="form-label" for="imagenes_carrusel">Agregar imágenes al carrusel:</label>
                <input type="file" name="imagenes_carrusel[]" id="imagenes_carrusel" class="form-control"
                       accept=".jpg,.jpeg,.png,.webp" multiple required>
                <small class="form-text-admin">
                    Puedes seleccionar varias a la vez. Formatos: JPG, JPEG, PNG o WEBP, máximo 5 MB cada una.
                    Se ven mejor en formato horizontal (16:9).
                </small>
            </div>
            <div class="campo-editar">
                <label class="form-label" for="id_informacion">Al tocar la imagen, llevar a (opcional):</label>
                <select name="id_informacion" id="id_informacion" class="form-control">
                    <option value="0">Sin enlace</option>
                    <?php foreach ($informaciones as $idInfo => $tituloInfo): ?>
                        <option value="<?= $idInfo ?>"><?= htmlspecialchars($tituloInfo) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text-admin">
                    Sirve para anunciar eventos: la imagen baja directo a la tarjeta de Información Institucional que elijas.
                    Si subes varias a la vez, todas llevarán al mismo lugar; después puedes cambiarlo imagen por imagen.
                </small>
            </div>
            <div class="botones-editar">
                <button type="submit" name="agregar_carrusel" class="btn-admin">Subir imágenes</button>
            </div>
        </form>

        <div class="carrusel-admin-lista">
            <span class="seccion-admin-preview-label">
                Imágenes actuales (<?= $resultadoCarrusel->num_rows ?>)
            </span>

            <?php if ($resultadoCarrusel->num_rows > 0): ?>
                <div class="carrusel-admin-grid">
                    <?php while ($imagen = $resultadoCarrusel->fetch_assoc()): ?>
                        <div class="carrusel-admin-item">
                            <img src="../../<?= htmlspecialchars($imagen['imagen']) ?>" alt="Imagen del carrusel" class="seccion-admin-img">

                            <form action="" method="POST" class="carrusel-admin-enlace">
                                <input type="hidden" name="id_imagen" value="<?= (int) $imagen['id'] ?>">
                                <select name="id_informacion" class="form-control" aria-label="Información a la que lleva esta imagen">
                                    <option value="0">Sin enlace</option>
                                    <?php foreach ($informaciones as $idInfo => $tituloInfo): ?>
                                        <option value="<?= $idInfo ?>"<?= (int) $imagen['id_informacion'] === $idInfo ? ' selected' : '' ?>><?= htmlspecialchars($tituloInfo) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="guardar_enlace" class="btn-admin">Guardar enlace</button>
                            </form>

                            <form action="" method="POST">
                                <input type="hidden" name="id_imagen" value="<?= (int) $imagen['id'] ?>">
                                <details class="confirmar-eliminar">
                                    <summary class="btn-eliminar">Eliminar</summary>
                                    <small class="form-text-admin">¿Seguro que quieres quitarla del carrusel?</small>
                                    <button type="submit" name="eliminar_carrusel" class="btn-eliminar">Sí, eliminar</button>
                                </details>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="form-text-admin">Aún no hay imágenes en el carrusel. El index no mostrará ninguna hasta que subas una.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid-secciones-admin">
        <?php while ($seccion = $resultado->fetch_assoc()): ?>
            <div class="seccion-admin-card">
                <div class="seccion-admin-header">
                    <h3>🖼️ <?= htmlspecialchars($seccion['titulo']) ?></h3>
                </div>

                <div class="seccion-admin-layout">
                    <div class="seccion-admin-form">
                        <form action="" method="POST">
                            <input type="hidden" name="id" value="<?= $seccion['id'] ?>">

                            <div class="campo-editar">
                                <label class="form-label">Título de la sección:</label>
                                <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($seccion['titulo']) ?>" required>
                            </div>

                            <div class="campo-editar">
                                <label class="form-label">Descripción:</label>
                                <textarea name="descripcion" rows="3" class="form-control" required><?= htmlspecialchars($seccion['descripcion']) ?></textarea>
                            </div>

                            <div class="botones-editar">
                                <button type="submit" name="editar" class="btn-admin">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<?php include '../../includes/PiePagina.php'; ?>
</body>
</html>