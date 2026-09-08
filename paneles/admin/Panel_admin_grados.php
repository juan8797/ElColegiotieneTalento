<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

require_once '../../conexion/db.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $stmt = $conexion->prepare("INSERT INTO grados (nombre, categoria) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $categoria);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Grado agregado correctamente";
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM grados WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Grado eliminado correctamente";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id        = $_POST['id'];
    $nombre    = $_POST['nombre_editar'];
    $categoria = $_POST['categoria_editar'];
    $stmt = $conexion->prepare("UPDATE grados SET nombre = ?, categoria = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $categoria, $id);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Grado actualizado correctamente";
}

$resultado = $conexion->query("SELECT * FROM grados ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_admin.php'; ?>

<main class="container-fluid">
    <div class="encabezado-panel">
        <h2>Panel Administrador — Gestión de Grados</h2>
    </div>
    <div class="explanation-table">
        <p class="text-explanation">Señor administrador le informo que este apartado tiene como objetivo perminitirle ingresar los frupo y grados de los cuales dispone la institucion en el año lectivo actual en esta podra eliminar o editar grupos anteriormente ingresados.</p>
    </div>
    <?php if ($mensaje): ?>
        <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <div class="form-admin">
        <h3>Agregar nuevo grado</h3>
        <form action="" method="POST">
            <input type="text" name="nombre" placeholder="Ej: 11-01" required>
            <select name="categoria" required>
                <option value="">-- Selecciona categoría --</option>
                <option value="primaria">Primaria</option>
                <option value="pre-juvenil">Pre-juvenil</option>
                <option value="juvenil">Juvenil</option>
            </select>
            <button type="submit" name="agregar">Agregar</button>
        </form>
    </div>

    <table class="tabla-participaciones">
        <thead>
            <tr>
                <th>ID</th>
                <th>Grado y Grupo</th>
                <th>Categoría</th>
                <th>Editar</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($grado = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $grado['id'] ?></td>
                <td><?= htmlspecialchars($grado['nombre']) ?></td>
                <td><?= htmlspecialchars($grado['categoria'] ?? '—') ?></td>
                <td>
                    <form action="" method="POST">
                        <input type="hidden" name="id" value="<?= $grado['id'] ?>">
                        <input type="text" name="nombre_editar"
                               value="<?= htmlspecialchars($grado['nombre']) ?>">
                        <select name="categoria_editar" required>
                            <option value="primaria" <?= $grado['categoria'] === 'primaria' ? 'selected' : '' ?>>Primaria</option>
                            <option value="pre-juvenil" <?= $grado['categoria'] === 'pre-juvenil' ? 'selected' : '' ?>>Pre-juvenil</option>
                            <option value="juvenil" <?= $grado['categoria'] === 'juvenil' ? 'selected' : '' ?>>Juvenil</option>
                        </select>
                        <button type="submit" name="editar">Guardar</button>
                    </form>
                </td>
                <td>
                    <a href="?eliminar=<?= $grado['id'] ?>">
                       Eliminar
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include '../../includes/PiePagina.php'; ?>

</body>
</html>