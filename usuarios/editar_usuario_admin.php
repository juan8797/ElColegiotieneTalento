<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

require_once '../conexion/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../paneles/panel_admin_usuarios.php");
    exit();
}

$mensaje = "";

// Procesar edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $_POST['nombre'];
    $apellido  = $_POST['apellido'];
    $correo    = $_POST['correo'];
    $rol       = $_POST['rol'];
    $grado_id  = !empty($_POST['grado_id']) ? $_POST['grado_id'] : NULL;

    // Si dejó la contraseña vacía no la cambia
    if (!empty($_POST['contrasena'])) {
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, apellido=?, correo=?, rol=?, grado_id=?, contrasena=? WHERE id=?");
        $stmt->bind_param("ssssssi", $nombre, $apellido, $correo, $rol, $grado_id, $contrasena, $id);
    } else {
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, apellido=?, correo=?, rol=?, grado_id=? WHERE id=?");
        $stmt->bind_param("sssssi", $nombre, $apellido, $correo, $rol, $grado_id, $id);
    }

    $stmt->execute();
    $stmt->close();
    $mensaje = "Usuario actualizado correctamente";
}

// Cargar datos del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Cargar grados
$grados = $conexion->query("SELECT * FROM grados ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Panel Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/menu_admin.php'; ?>

<main class="container-fluid">
    <div class="seccion-usuarios">
        <h2>Editar Usuario</h2>

        <?php if ($mensaje): ?>
            <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="campo-editar">
                <label>Nombre:</label>
                <input type="text" name="nombre" 
                       value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                       class="form-control" required>
            </div>

            <div class="campo-editar">
                <label>Apellido:</label>
                <input type="text" name="apellido" 
                       value="<?= htmlspecialchars($usuario['apellido']) ?>" 
                       class="form-control" required>
            </div>

            <div class="campo-editar">
                <label>Correo:</label>
                <input type="email" name="correo" 
                       value="<?= htmlspecialchars($usuario['correo']) ?>" 
                       class="form-control" required>
            </div>

            <div class="campo-editar">
                <label>Rol:</label>
                <select name="rol" class="form-control">
                    <option value="estudiante" <?= $usuario['rol'] === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                    <option value="docente"    <?= $usuario['rol'] === 'docente'    ? 'selected' : '' ?>>Docente</option>
                    <option value="jurado"     <?= $usuario['rol'] === 'jurado'     ? 'selected' : '' ?>>Jurado</option>
                    <option value="admin"      <?= $usuario['rol'] === 'admin'      ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <div class="campo-editar">
                <label>Grado y Grupo:</label>
                <select name="grado_id" class="form-control">
                    <option value="">Sin grado asignado</option>
                    <?php while ($g = $grados->fetch_assoc()): ?>
                        <option value="<?= $g['id'] ?>" 
                            <?= $usuario['grado_id'] == $g['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo-editar">
                <label>Nueva contraseña: <small>(déjalo vacío para no cambiarla)</small></label>
                <input type="password" name="contrasena" class="form-control">
            </div>

            <div class="botones-editar">
                <button type="submit" class="btn-admin">Guardar cambios</button>
                <a href="../paneles/panel_admin_usuarios.php" class="btn-eliminar">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/PiePagina.php'; ?>

</body>
</html>