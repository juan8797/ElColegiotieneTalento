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

// Sube las imágenes adjuntas de una información. Devuelve [subidas, rechazadas].
function subirImagenesInformacion($conexion, $idInformacion, $archivos, $carpetaImagenes) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $tamanoMaximo = 5 * 1024 * 1024; // 5 MB por imagen
    $subidas = 0;
    $rechazadas = 0;

    if (!isset($archivos['name']) || !is_array($archivos['name'])) {
        return [0, 0];
    }

    $stmt = $conexion->prepare("INSERT INTO informacion_imagenes (id_informacion, imagen) VALUES (?, ?)");

    foreach ($archivos['name'] as $i => $nombreOriginal) {
        if ($archivos['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($archivos['error'][$i] !== UPLOAD_ERR_OK || $archivos['size'][$i] > $tamanoMaximo) {
            $rechazadas++;
            continue;
        }

        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $esImagenValida = in_array($extension, $extensionesPermitidas)
            && @getimagesize($archivos['tmp_name'][$i]) !== false;

        if (!$esImagenValida) {
            $rechazadas++;
            continue;
        }

        $nombreArchivo = 'Info_' . $idInformacion . '_' . time() . '_' . $i . '_' . bin2hex(random_bytes(3)) . '.' . $extension;

        if (move_uploaded_file($archivos['tmp_name'][$i], $carpetaImagenes . $nombreArchivo)) {
            $rutaImagen = 'img/' . $nombreArchivo;
            $stmt->bind_param("is", $idInformacion, $rutaImagen);
            $stmt->execute();
            $subidas++;
        } else {
            $rechazadas++;
        }
    }

    $stmt->close();
    return [$subidas, $rechazadas];
}

// Texto que resume cuántas imágenes se subieron o se rechazaron
function resumenImagenes($subidas, $rechazadas) {
    $texto = "";
    if ($subidas > 0) {
        $texto .= " Imágenes adjuntadas: $subidas.";
    }
    if ($rechazadas > 0) {
        $texto .= " No se pudieron subir $rechazadas imagen(es): usa jpg, jpeg, png o webp de máximo 5 MB.";
    }
    return $texto;
}

// Borra el archivo físico de la carpeta img (solo si realmente está dentro de ella)
function borrarArchivoImagen($rutaRelativa, $carpetaImagenes) {
    $archivoReal = realpath('../../' . $rutaRelativa);
    $carpetaReal = realpath($carpetaImagenes);

    if ($archivoReal !== false && $carpetaReal !== false
        && strpos($archivoReal, $carpetaReal) === 0 && is_file($archivoReal)) {
        unlink($archivoReal);
    }
}

// Si el servidor rechaza la subida por tamaño total, PHP deja $_POST y $_FILES vacíos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $mensaje = "Las imágenes superan el tamaño total que permite el servidor. Sube menos imágenes a la vez.";
    $claseMensaje = "mensaje-admin mensaje-error";
}

// Agregar información (con imágenes adjuntas opcionales)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $stmt = $conexion->prepare("INSERT INTO informacion_institucional (titulo, contenido) VALUES (?, ?)");
    $stmt->bind_param("ss", $titulo, $contenido);
    $stmt->execute();
    $idNueva = $conexion->insert_id;
    $stmt->close();

    $mensaje = "Información agregada correctamente.";

    if (isset($_FILES['imagenes_info'])) {
        [$subidas, $rechazadas] = subirImagenesInformacion($conexion, $idNueva, $_FILES['imagenes_info'], $carpetaImagenes);
        $mensaje .= resumenImagenes($subidas, $rechazadas);
        if ($rechazadas > 0) {
            $claseMensaje = "mensaje-admin mensaje-error";
        }
    }
}

// Eliminar una información completa (y sus imágenes adjuntas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_info'])) {
    $id = (int) $_POST['id'];

    $stmt = $conexion->prepare("SELECT imagen FROM informacion_imagenes WHERE id_informacion = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $adjuntas = $stmt->get_result();
    while ($adjunta = $adjuntas->fetch_assoc()) {
        borrarArchivoImagen($adjunta['imagen'], $carpetaImagenes);
    }
    $stmt->close();

    $stmt = $conexion->prepare("DELETE FROM informacion_imagenes WHERE id_informacion = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conexion->prepare("DELETE FROM informacion_institucional WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $mensaje = "Información eliminada correctamente";
}

// Editar título/contenido y, si se eligen, agregar más imágenes adjuntas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = (int) $_POST['id'];
    $titulo = $_POST['titulo_editar'];
    $contenido = $_POST['contenido_editar'];
    $stmt = $conexion->prepare("UPDATE informacion_institucional SET titulo = ?, contenido = ? WHERE id = ?");
    $stmt->bind_param("ssi", $titulo, $contenido, $id);
    $stmt->execute();
    $stmt->close();

    $mensaje = "Información actualizada correctamente.";

    if (isset($_FILES['imagenes_info_editar'])) {
        [$subidas, $rechazadas] = subirImagenesInformacion($conexion, $id, $_FILES['imagenes_info_editar'], $carpetaImagenes);
        $mensaje .= resumenImagenes($subidas, $rechazadas);
        if ($rechazadas > 0) {
            $claseMensaje = "mensaje-admin mensaje-error";
        }
    }
}

// Quitar una imagen adjunta (de la base de datos y de la carpeta img)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_imagen_info'])) {
    $idImagen = (int) $_POST['id_imagen'];

    $stmt = $conexion->prepare("SELECT imagen FROM informacion_imagenes WHERE id = ?");
    $stmt->bind_param("i", $idImagen);
    $stmt->execute();
    $imagenEliminar = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($imagenEliminar) {
        borrarArchivoImagen($imagenEliminar['imagen'], $carpetaImagenes);

        $stmt = $conexion->prepare("DELETE FROM informacion_imagenes WHERE id = ?");
        $stmt->bind_param("i", $idImagen);
        $stmt->execute();
        $stmt->close();

        $mensaje = "Imagen adjunta eliminada.";
    }
}

$resultado = $conexion->query("SELECT * FROM informacion_institucional ORDER BY fecha_publicacion DESC");

// Imágenes adjuntas agrupadas por información
$imagenesPorInfo = [];
$resultadoImagenes = $conexion->query("SELECT id, id_informacion, imagen FROM informacion_imagenes ORDER BY id ASC");
while ($fila = $resultadoImagenes->fetch_assoc()) {
    $imagenesPorInfo[(int) $fila['id_informacion']][] = $fila;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Información Institucional</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_admin.php'; ?>

<main class="container-fluid">
    <div class="encabezado-panel">
        <h2>Panel Administrador — Información Institucional</h2>
    </div>
    <div class="explanation-table">
        <p class="text-explanation">
            Aquí puedes publicar avisos, cambios del festival o información del colegio
            que no esté necesariamente relacionada con el festival de talentos.
            Esto se mostrará en la página principal del sitio.
        </p>
    </div>

    <?php if ($mensaje): ?>
        <p class="<?= $claseMensaje ?>" role="alert"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <div class="form-admin">
        <h3>➕ Agregar nueva información institucional</h3>
        <form action="" method="POST" enctype="multipart/form-data" class="form-informacion-nueva">
            <div class="campo-editar">
                <label class="form-label">Título:</label>
                <input type="text" name="titulo" class="form-control" placeholder="Título de la información o aviso" required>
            </div>
            <div class="campo-editar">
                <label class="form-label">Contenido:</label>
                <textarea name="contenido" rows="3" class="form-control" placeholder="Escriba aquí el contenido detallado..." required></textarea>
            </div>
            <div class="campo-editar">
                <label class="form-label" for="imagenes_info">Imágenes adjuntas (opcional):</label>
                <input type="file" name="imagenes_info[]" id="imagenes_info" class="form-control"
                       accept=".jpg,.jpeg,.png,.webp" multiple>
                <small class="form-text-admin">Puedes seleccionar varias. Formatos: JPG, JPEG, PNG o WEBP, máximo 5 MB cada una.</small>
            </div>
            <div class="botones-editar" style="margin-top: 15px;">
                <button type="submit" name="agregar" class="btn-admin">Publicar Información</button>
            </div>
        </form>
    </div>

    <section class="seccion-usuarios">
        <h3>Avisos e Informaciones Registradas</h3>
        <div class="tabla-responsive">
            <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th style="width: 22%;">Título</th>
                    <th>Contenido</th>
                    <th style="width: 140px; text-align: center;">Fecha</th>
                    <th style="width: 250px;">Edición Rápida</th>
                    <th style="width: 100px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($info = $resultado->fetch_assoc()): ?>
                <?php $adjuntas = $imagenesPorInfo[(int) $info['id']] ?? []; ?>
                <tr>
                    <td><strong><?= htmlspecialchars($info['titulo']) ?></strong></td>
                    <td><div class="texto-contenido-tabla"><?= nl2br(htmlspecialchars($info['contenido'])) ?></div></td>
                    <td style="text-align: center;">
                        <span class="badge-fecha"><?= date('d/m/Y H:i', strtotime($info['fecha_publicacion'])) ?></span>
                    </td>
                    <td>
                        <form action="" method="POST" enctype="multipart/form-data" class="form-edicion-inline">
                            <input type="hidden" name="id" value="<?= (int) $info['id'] ?>">
                            <input type="text" name="titulo_editar" class="form-control form-control-sm mb-1" value="<?= htmlspecialchars($info['titulo']) ?>" placeholder="Título" required>
                            <textarea name="contenido_editar" rows="2" class="form-control form-control-sm mb-1" placeholder="Contenido" required><?= htmlspecialchars($info['contenido']) ?></textarea>
                            <small class="form-text-admin">Agregar más imágenes:</small>
                            <input type="file" name="imagenes_info_editar[]" class="form-control form-control-sm mb-1"
                                   accept=".jpg,.jpeg,.png,.webp" multiple>
                            <button type="submit" name="editar" class="btn-admin btn-sm">Guardar cambios</button>
                        </form>

                        <?php if (!empty($adjuntas)): ?>
                            <div class="info-admin-imagenes">
                                <small class="form-text-admin">Imágenes adjuntas (<?= count($adjuntas) ?>)</small>
                                <div class="info-admin-miniaturas">
                                    <?php foreach ($adjuntas as $adjunta): ?>
                                        <div class="info-admin-miniatura">
                                            <img src="../../<?= htmlspecialchars($adjunta['imagen']) ?>" alt="Imagen adjunta">
                                            <form action="" method="POST">
                                                <input type="hidden" name="id_imagen" value="<?= (int) $adjunta['id'] ?>">
                                                <details class="confirmar-eliminar">
                                                    <summary class="btn-eliminar">Quitar</summary>
                                                    <button type="submit" name="eliminar_imagen_info" class="btn-eliminar">Sí, quitar</button>
                                                </details>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <form action="" method="POST">
                            <input type="hidden" name="id" value="<?= (int) $info['id'] ?>">
                            <details class="confirmar-eliminar">
                                <summary class="btn-eliminar">Eliminar</summary>
                                <small class="form-text-admin">¿Seguro? Se borrará también con sus imágenes.</small>
                                <button type="submit" name="eliminar_info" class="btn-eliminar">Sí, eliminar</button>
                            </details>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($resultado->num_rows === 0): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 25px; color: var(--color-text-muted);">
                        No hay publicaciones registradas aún.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</main>

<?php include '../../includes/PiePagina.php'; ?>
</body>
</html>