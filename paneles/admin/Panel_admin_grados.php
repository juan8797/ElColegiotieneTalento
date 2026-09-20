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
        <h3>➕ Agregar nuevo grado</h3>
        <form action="" method="POST" class="form-admin-inline">
            <div class="campo-inline">
                <label class="form-label-inline">Grado y Grupo:</label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: 11-01" required>
            </div>
            <div class="campo-inline">
                <label class="form-label-inline">Categoría:</label>
                <select name="categoria" class="form-control" required>
                    <option value="">-- Selecciona categoría --</option>
                    <option value="primaria">Primaria</option>
                    <option value="pre-juvenil">Pre-juvenil</option>
                    <option value="juvenil">Juvenil</option>
                </select>
            </div>
            <div class="campo-inline-btn">
                <button type="submit" name="agregar" class="btn-admin">Agregar Grado</button>
            </div>
        </form>
    </div>

    <section class="seccion-usuarios">
        <h3>Grados y Grupos Registrados</h3>
        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th style="width: 70px; text-align: center;">ID</th>
                    <th>Grado y Grupo</th>
                    <th style="text-align: center;">Categoría</th>
                    <th style="min-width: 320px;">Edición Rápida</th>
                    <th style="width: 100px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($grado = $resultado->fetch_assoc()): ?>
                <tr>
                    <td style="text-align: center;"><strong>#<?= $grado['id'] ?></strong></td>
                    <td><strong><?= htmlspecialchars($grado['nombre']) ?></strong></td>
                    <td style="text-align: center;">
                        <span class="badge-categoria badge-cat-<?= htmlspecialchars($grado['categoria'] ?? '') ?>">
                            <?= htmlspecialchars(ucfirst($grado['categoria'] ?? '—')) ?>
                        </span>
                    </td>
                    <td>
                        <form action="" method="POST" class="form-edicion-inline-grado">
                            <input type="hidden" name="id" value="<?= $grado['id'] ?>">
                            <input type="text" name="nombre_editar" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($grado['nombre']) ?>" placeholder="Grado" required>
                            <select name="categoria_editar" class="form-control form-control-sm" required>
                                <option value="primaria" <?= $grado['categoria'] === 'primaria' ? 'selected' : '' ?>>Primaria</option>
                                <option value="pre-juvenil" <?= $grado['categoria'] === 'pre-juvenil' ? 'selected' : '' ?>>Pre-juvenil</option>
                                <option value="juvenil" <?= $grado['categoria'] === 'juvenil' ? 'selected' : '' ?>>Juvenil</option>
                            </select>
                            <button type="submit" name="editar" class="btn-admin btn-sm">Guardar</button>
                        </form>
                    </td>
                    <td style="text-align: center;">
                        <a href="?eliminar=<?= $grado['id'] ?>" class="btn-eliminar"
                           onclick="return confirm('¿Seguro que quieres eliminar este grado?')">
                           Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($resultado->num_rows === 0): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 25px; color: var(--color-text-muted);">
                        No hay grados registrados aún.
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