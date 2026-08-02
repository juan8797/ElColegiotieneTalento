<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jurado') {
    header("Location: ../login/login.php");
    exit();
}

include '../conexion/db.php';

// Procesar observación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_participacion    = $_POST['id_participacion'];
    $observacion_jurado  = $_POST['observacion_jurado'];

    $stmt = $conexion->prepare("UPDATE participaciones SET observacion_jurado = ? WHERE id = ?");
    $stmt->bind_param("si", $observacion_jurado, $id_participacion);
    $stmt->execute();
    $stmt->close();
}

// Consulta JOIN — solo estudiantes con talento_individual aprobados
$sql = "SELECT p.id, u.nombre, u.apellido, g.nombre AS grado, p.nombre_acto, p.observacion_jurado
        FROM participaciones p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN grados g ON u.grado_id = g.id
        WHERE p.modalidad = 'talento_individual'
        AND p.estado = 'aprobado'
        ORDER BY u.apellido ASC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Jurado</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="encabezado-panel">
    <h1>Bienvenido jurado, <?= $_SESSION['nombre'] ?></h1>
    <div class="lado-derecho-panel">
        <a href="../usuarios/editarPerfil.php"><button class="btn-editar">Editar Perfil</button></a>
        <a href="../login/login.php"><button class="btn-editar">Cerrar sesion</button></a>
    </div>
</div>

<main class="container-fluid">

    <!-- Explicación -->
    <div class="explanation-table">
        <p class="text-explanation">
            Estimado jurado, <?= $_SESSION['nombre'] ?>, en este panel podrás ver los estudiantes 
            que participarán en la modalidad de <strong>Talento Individual</strong> y han sido 
            aprobados por su docente. Puedes dejar una observación o consejo para cada participante.
        </p>
    </div>

    <!-- Tabla talento individual -->
    <section class="seccion-usuarios">
        <h3>Participantes — Talento Individual</h3>
        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Grado</th>
                    <th>Nombre del acto</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['apellido']) ?></td>
                    <td><?= htmlspecialchars($p['grado'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['nombre_acto'] ?? '—') ?></td>
                    <td>
                        <form action="" method="POST">
                            <input type="hidden" name="id_participacion" value="<?= $p['id'] ?>">
                            <textarea name="observacion_jurado" 
                                      rows="2" 
                                      placeholder="Escribe una observación..."
                                      class="form-observacion"><?= htmlspecialchars($p['observacion_jurado'] ?? '') ?></textarea>
                            <button type="submit" class="btn-guardar-obs">Guardar</button>
                        </form>
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