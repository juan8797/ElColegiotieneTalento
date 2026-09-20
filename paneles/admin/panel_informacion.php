<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

require_once '../../conexion/db.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $stmt = $conexion->prepare("INSERT INTO informacion_institucional (titulo, contenido) VALUES (?, ?)");
    $stmt->bind_param("ss", $titulo, $contenido);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Información agregada correctamente";
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM informacion_institucional WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Información eliminada correctamente";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = $_POST['id'];
    $titulo = $_POST['titulo_editar'];
    $contenido = $_POST['contenido_editar'];
    $stmt = $conexion->prepare("UPDATE informacion_institucional SET titulo = ?, contenido = ? WHERE id = ?");
    $stmt->bind_param("ssi", $titulo, $contenido, $id);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Información actualizada correctamente";
}

$resultado = $conexion->query("SELECT * FROM informacion_institucional ORDER BY fecha_publicacion DESC");
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
        <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <div class="form-admin">
        <h3>➕ Agregar nueva información institucional</h3>
        <form action="" method="POST" class="form-informacion-nueva">
            <div class="campo-editar">
                <label class="form-label">Título:</label>
                <input type="text" name="titulo" class="form-control" placeholder="Título de la información o aviso" required>
            </div>
            <div class="campo-editar">
                <label class="form-label">Contenido:</label>
                <textarea name="contenido" rows="3" class="form-control" placeholder="Escriba aquí el contenido detallado..." required></textarea>
            </div>
            <div class="botones-editar" style="margin-top: 15px;">
                <button type="submit" name="agregar" class="btn-admin">Publicar Información</button>
            </div>
        </form>
    </div>

    <section class="seccion-usuarios">
        <h3>Avisos e Informaciones Registradas</h3>
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
                <tr>
                    <td><strong><?= htmlspecialchars($info['titulo']) ?></strong></td>
                    <td><div class="texto-contenido-tabla"><?= nl2br(htmlspecialchars($info['contenido'])) ?></div></td>
                    <td style="text-align: center;">
                        <span class="badge-fecha"><?= date('d/m/Y H:i', strtotime($info['fecha_publicacion'])) ?></span>
                    </td>
                    <td>
                        <form action="" method="POST" class="form-edicion-inline">
                            <input type="hidden" name="id" value="<?= $info['id'] ?>">
                            <input type="text" name="titulo_editar" class="form-control form-control-sm mb-1" value="<?= htmlspecialchars($info['titulo']) ?>" placeholder="Título" required>
                            <textarea name="contenido_editar" rows="2" class="form-control form-control-sm mb-1" placeholder="Contenido" required><?= htmlspecialchars($info['contenido']) ?></textarea>
                            <button type="submit" name="editar" class="btn-admin btn-sm">Guardar cambios</button>
                        </form>
                    </td>
                    <td style="text-align: center;">
                        <a href="?eliminar=<?= $info['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar esta información?')">Eliminar</a>
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
    </section>
</main>

<?php include '../../includes/PiePagina.php'; ?>
</body>
</html>