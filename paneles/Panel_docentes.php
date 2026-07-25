<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../login/login.php");
    exit();
}

require_once '../conexion/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = $_POST['id'];
    $estado     = $_POST['estado'];
    $comentario = $_POST['comentario'];

    $stmt = $conexion->prepare("UPDATE participaciones SET estado = ?, comentario = ? WHERE id = ?");
    $stmt->bind_param("ssi", $estado, $comentario, $id);
    $stmt->execute();
    $stmt->close();
}


$sql = "SELECT p.id, u.nombre, u.apellido, p.modalidad, p.nombre_acto, p.estado, p.comentario
        FROM participaciones p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        WHERE u.rol = 'estudiante'
        ORDER BY p.estado ASC, u.apellido ASC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<main class="container-fluid">
    <div class="encabezado-panel">
        <h1>Bienvenido docente, <?php echo $_SESSION['nombre']; ?></h1>
        <a href="../usuarios/editarPerfil.php"><button class="btn-editar">Editar Perfil</button></a>
        <a href="../login/login.php"><button class="btn-editar">Cerrar sesion</button></a>
    </div>
    <div class="explanation-table">
        <p class="text-explanation">Estimado docente, <?php echo $_SESSION['nombre']; ?>  la intencion de la tabla acontinuacon es demostrar los estudiantes que van a participar en el festival y en que van a participar. su labor sera aprobar o rechasar la solcitud de participacon del estudiante dependiendo como considere que se encuentra su acto en el caso de que consedere nesesario aportar con un comentario ouna sugerencia podra hacerlo.</p>
    </div>
        <table class="tabla-participaciones">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Modalidad</th>
                <th>Nombre del acto</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['apellido']) ?></td>
                <td><?= htmlspecialchars($p['modalidad']) ?></td>
                <td><?= htmlspecialchars($p['nombre_acto'] ?? '—') ?></td>
                <td class="estado-<?= $p['estado'] ?>">
                    <?= ucfirst($p['estado']) ?>
                </td>
                <td>
                    <form action="" method="POST">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <select name="estado">
                            <option value="pendiente" <?= $p['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="aprobado"  <?= $p['estado'] === 'aprobado'  ? 'selected' : '' ?>>Aprobado</option>
                            <option value="rechazado" <?= $p['estado'] === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                        </select>
                        <input type="text" name="comentario"
                               placeholder="Comentario..."
                               value="<?= htmlspecialchars($p['comentario'] ?? '') ?>">
                        <button type="submit" class="btn-editar">Guardar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/PiePagina.php'; ?>

</body>
</html>