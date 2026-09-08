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
        <h3>Agregar nueva información</h3>
        <form action="" method="POST">
            <input type="text" name="titulo" placeholder="Título" required><br><br>
            <textarea name="contenido" rows="3" placeholder="Contenido" required></textarea><br><br>
            <button type="submit" name="agregar">Publicar</button>
        </form>
    </div>

    <table class="tabla-participaciones">
        <thead>
            <tr>
                <th>Título</th>
                <th>Contenido</th>
                <th>Fecha</th>
                <th>Editar</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($info = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($info['titulo']) ?></td>
                <td><?= htmlspecialchars($info['contenido']) ?></td>
                <td><?= htmlspecialchars($info['fecha_publicacion']) ?></td>
                <td>
                    <form action="" method="POST">
                        <input type="hidden" name="id" value="<?= $info['id'] ?>">
                        <input type="text" name="titulo_editar" value="<?= htmlspecialchars($info['titulo']) ?>"><br>
                        <textarea name="contenido_editar" rows="2"><?= htmlspecialchars($info['contenido']) ?></textarea><br>
                        <button type="submit" name="editar">Guardar</button>
                    </form>
                </td>
                <td>
                    <a href="?eliminar=<?= $info['id'] ?>">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/PiePagina.php'; ?>
</body>
</html>