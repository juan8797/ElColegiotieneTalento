<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

require_once '../conexion/db.php';

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$estudiantes = $conexion->query("SELECT u.id, u.nombre, u.apellido, u.correo, g.nombre AS grado 
                                  FROM usuarios u 
                                  LEFT JOIN grados g ON u.grado_id = g.id 
                                  WHERE u.rol = 'estudiante' 
                                  ORDER BY u.apellido ASC");

$docentes = $conexion->query("SELECT u.id, u.nombre, u.apellido, u.correo, g.nombre AS grado 
                               FROM usuarios u 
                               LEFT JOIN grados g ON u.grado_id = g.id 
                               WHERE u.rol = 'docente' 
                               ORDER BY u.apellido ASC");

$jurados = $conexion->query("SELECT id, nombre, apellido, correo 
                              FROM usuarios 
                              WHERE rol = 'jurado' 
                              ORDER BY apellido ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Panel Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/menu_admin.php'; ?>

<main class="container-fluid">

    <h2 class="titulo-seccion">Usuarios Registrados</h2>

    <!-- Estudiantes -->
    <section class="seccion-usuarios">
        <h3>Estudiantes</h3>
        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Grado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($e = $estudiantes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($e['nombre']) ?></td>
                    <td><?= htmlspecialchars($e['apellido']) ?></td>
                    <td><?= htmlspecialchars($e['correo']) ?></td>
                    <td><?= htmlspecialchars($e['grado'] ?? '—') ?></td>
                    <td>
                        <a href="/ElColegiotieneTalento/usuarios/editar_usuario_admin.php?id=<?= $e['id'] ?>" 
                           class="btn-editar">Editar</a>
                        <a href="?eliminar=<?= $e['id'] ?>"
                           onclick="return confirm('¿Seguro que quieres eliminar este usuario?')"
                           class="btn-eliminar">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <!-- Docentes -->
    <section class="seccion-usuarios">
        <h3>Docentes</h3>
        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Grado a cargo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($d = $docentes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nombre']) ?></td>
                    <td><?= htmlspecialchars($d['apellido']) ?></td>
                    <td><?= htmlspecialchars($d['correo']) ?></td>
                    <td><?= htmlspecialchars($d['grado'] ?? '—') ?></td>
                    <td>
                        <a href="/ElColegiotieneTalento/usuarios/editar_usuario_admin.php?id=<?= $d['id'] ?>" 
                           class="btn-editar">Editar</a>
                        <a href="?eliminar=<?= $d['id'] ?>"
                           onclick="return confirm('¿Seguro que quieres eliminar este usuario?')"
                           class="btn-eliminar">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <!-- Jurados -->
    <section class="seccion-usuarios">
        <h3>Jurados</h3>
        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($j = $jurados->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($j['nombre']) ?></td>
                    <td><?= htmlspecialchars($j['apellido']) ?></td>
                    <td><?= htmlspecialchars($j['correo']) ?></td>
                    <td>
                        <a href="/ElColegiotieneTalento/usuarios/editar_usuario_admin.php?id=<?= $j['id'] ?>" 
                           class="btn-editar">Editar</a>
                        <a href="?eliminar=<?= $j['id'] ?>"
                           onclick="return confirm('¿Seguro que quieres eliminar este usuario?')"
                           class="btn-eliminar">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

</main>

<?php include '../includes/PiePagina.php'; ?>

</body>
</html>