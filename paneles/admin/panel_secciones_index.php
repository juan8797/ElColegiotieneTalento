<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

require_once '../../conexion/db.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id          = $_POST['id'];
    $titulo      = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];

    $stmt = $conexion->prepare("UPDATE secciones_index SET titulo = ?, descripcion = ? WHERE id = ?");
    $stmt->bind_param("ssi", $titulo, $descripcion, $id);
    $stmt->execute();
    $stmt->close();

    if ($mensaje === "") {
        $mensaje = "Sección actualizada correctamente.";
    }
}

// Guardar/cambiar la imagen principal (única sección con imagen en este panel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_imagen_principal'])) {
    $stmtActual = $conexion->prepare("SELECT imagen FROM secciones_index WHERE clave = 'imagen_principal'");
    $stmtActual->execute();
    $imagenActual = $stmtActual->get_result()->fetch_assoc()['imagen'] ?? '';
    $stmtActual->close();

    $rutaImagen = $imagenActual;

    if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['imagen_principal']['name'], PATHINFO_EXTENSION));

        if (in_array($extension, $extensionesPermitidas)) {
            $nombreArchivo = 'ImagenPrincipal_' . time() . '.' . $extension;
            $rutaDestino = '../../img/' . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $rutaDestino)) {
                $rutaImagen = 'img/' . $nombreArchivo;
            } else {
                $mensaje = "No se pudo subir la imagen principal, se mantuvo la anterior.";
            }
        } else {
            $mensaje = "Formato de imagen no permitido (usa jpg, jpeg, png o webp). No se cambió la imagen.";
        }
    }

    $stmt = $conexion->prepare("UPDATE secciones_index SET imagen = ? WHERE clave = 'imagen_principal'");
    $stmt->bind_param("s", $rutaImagen);
    $stmt->execute();
    $stmt->close();

    if ($mensaje === "") {
        $mensaje = "Imagen principal actualizada correctamente.";
    }
}

// Eliminar la imagen principal (queda sin imagen en el index hasta que se suba otra)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_imagen_principal'])) {
    $stmt = $conexion->prepare("UPDATE secciones_index SET imagen = '' WHERE clave = 'imagen_principal'");
    $stmt->execute();
    $stmt->close();
    $mensaje = "Imagen principal eliminada.";
}

$stmtImgPrincipal = $conexion->prepare("SELECT * FROM secciones_index WHERE clave = 'imagen_principal'");
$stmtImgPrincipal->execute();
$imagenPrincipal = $stmtImgPrincipal->get_result()->fetch_assoc();
$stmtImgPrincipal->close();

// El resto de secciones (título/descripción) se listan aparte, sin la fila de la imagen principal
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
            Desde aquí puedes cambiar la imagen principal del index, y el título
            y la descripción de las 3 tarjetas que aparecen en la página principal del sitio.
        </p>
    </div>

    <?php if ($mensaje): ?>
        <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <div class="seccion-admin-card">
        <div class="seccion-admin-header">
            <h3>🖼️ Imagen principal del index</h3>
        </div>

        <div class="seccion-admin-layout">
            <div class="seccion-admin-preview">
                <span class="seccion-admin-preview-label">Imagen actual</span>
                <?php if (!empty($imagenPrincipal['imagen'])): ?>
                    <img src="../../<?= htmlspecialchars($imagenPrincipal['imagen']) ?>" alt="Imagen principal" class="seccion-admin-img">
                <?php else: ?>
                    <p class="form-text-admin">No hay ninguna imagen asignada actualmente.</p>
                <?php endif; ?>
            </div>

            <div class="seccion-admin-form">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="campo-editar">
                        <label class="form-label">Cambiar imagen principal:</label>
                        <input type="file" name="imagen_principal" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <small class="form-text-admin">Formatos permitidos: JPG, JPEG, PNG, WEBP.</small>
                    </div>
                    <div class="botones-editar">
                        <button type="submit" name="guardar_imagen_principal" class="btn-admin">Guardar imagen</button>
                        <?php if (!empty($imagenPrincipal['imagen'])): ?>
                            <button type="submit" name="eliminar_imagen_principal" class="btn-eliminar"
                                    onclick="return confirm('¿Seguro que quieres eliminar la imagen principal del index?');">Eliminar imagen</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
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