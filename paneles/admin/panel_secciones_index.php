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

    // Traemos la imagen actual por si no se sube una nueva
    $stmtActual = $conexion->prepare("SELECT imagen FROM secciones_index WHERE id = ?");
    $stmtActual->bind_param("i", $id);
    $stmtActual->execute();
    $imagenActual = $stmtActual->get_result()->fetch_assoc()['imagen'];
    $stmtActual->close();

    $rutaImagen = $imagenActual;

    // Si el admin subió una imagen nueva, la procesamos
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

        if (in_array($extension, $extensionesPermitidas)) {
            $nombreArchivo = 'seccion_' . $id . '_' . time() . '.' . $extension;
            $rutaDestino = '../../img/' . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                $rutaImagen = 'img/' . $nombreArchivo;
            } else {
                $mensaje = "No se pudo subir la imagen, se mantuvo la anterior.";
            }
        } else {
            $mensaje = "Formato de imagen no permitido (usa jpg, jpeg, png o webp). No se cambió la imagen.";
        }
    }

    $stmt = $conexion->prepare("UPDATE secciones_index SET titulo = ?, descripcion = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("sssi", $titulo, $descripcion, $rutaImagen, $id);
    $stmt->execute();
    $stmt->close();

    if ($mensaje === "") {
        $mensaje = "Sección actualizada correctamente.";
    }
}

$resultado = $conexion->query("SELECT * FROM secciones_index ORDER BY id ASC");
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
            Desde aquí puedes cambiar el título, la descripción y la imagen de las 3
            tarjetas que aparecen en la página principal del sitio.
        </p>
    </div>

    <?php if ($mensaje): ?>
        <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php while ($seccion = $resultado->fetch_assoc()): ?>
        <div class="seccion-admin-card">
            <div class="seccion-admin-header">
                <h3>🖼️ <?= htmlspecialchars($seccion['titulo']) ?></h3>
            </div>

            <div class="seccion-admin-layout">
                <div class="seccion-admin-preview">
                    <span class="seccion-admin-preview-label">Imagen actual</span>
                    <img src="../../<?= htmlspecialchars($seccion['imagen']) ?>" alt="<?= htmlspecialchars($seccion['titulo']) ?>" class="seccion-admin-img">
                </div>

                <div class="seccion-admin-form">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $seccion['id'] ?>">

                        <div class="campo-editar">
                            <label class="form-label">Título de la sección:</label>
                            <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($seccion['titulo']) ?>" required>
                        </div>

                        <div class="campo-editar">
                            <label class="form-label">Descripción:</label>
                            <textarea name="descripcion" rows="3" class="form-control" required><?= htmlspecialchars($seccion['descripcion']) ?></textarea>
                        </div>

                        <div class="campo-editar">
                            <label class="form-label">Cambiar imagen (opcional):</label>
                            <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            <small class="form-text-admin">Formatos permitidos: JPG, JPEG, PNG, WEBP.</small>
                        </div>

                        <div class="botones-editar">
                            <button type="submit" name="editar" class="btn-admin">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</main>

<?php include '../../includes/PiePagina.php'; ?>
</body>
</html>