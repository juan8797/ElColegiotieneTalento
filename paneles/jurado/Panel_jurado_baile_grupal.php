<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jurado') {
    header("Location: ../login/login.php");
    exit();
}

include '../../conexion/db.php';

$sql = "SELECT g.id AS grado_id, g.nombre AS grado_nombre,
               COUNT(DISTINCT p.usuario_id) AS num_estudiantes,
               (SELECT COUNT(*) FROM calificaciones c
                WHERE c.grado_id = g.id AND c.jurado_id = ?) AS criterios_calificados,
               (SELECT COUNT(*) FROM criterios) AS total_criterios
        FROM participaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        JOIN grados g ON u.grado_id = g.id
        WHERE p.modalidad = 'baile_grupal'
          AND p.estado = 'aprobado'
        GROUP BY g.id, g.nombre
        ORDER BY g.nombre ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Jurado - Baile Grupal</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_jurado.php'; ?>

    <div class="encabezado-panel">
        <h1>Baile Grupal</h1>
        <div class="lado-derecho-panel">
            <a href="../../usuarios/editarPerfil.php"><button class="btn-editar">Editar Perfil</button></a>
        </div>
    </div>

    <main class="container-fluid">
        <div class="explanation-table">
            <p class="text-explanation">
                Estimado jurado, <?= $_SESSION['nombre'] ?>, aquí puedes ver los grados que participan
                en baile grupal y calificarlos según los criterios definidos por el administrador.
            </p>
        </div>

        <section class="seccion-usuarios">
            <table class="tabla-participaciones">
                <thead>
                    <tr>
                        <th>Grado</th>
                        <th>Estudiantes</th>
                        <th>Estado de tu calificación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['grado_nombre']) ?></td>
                            <td><?= (int)$fila['num_estudiantes'] ?></td>
                            <td>
                                <?php if ($fila['total_criterios'] == 0): ?>
                                    <span class="estado-pendiente">Sin criterios definidos</span>
                                <?php elseif ($fila['criterios_calificados'] >= $fila['total_criterios']): ?>
                                    <span class="estado-aprobado">Calificado</span>
                                <?php else: ?>
                                    <span class="estado-pendiente">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="calificar_grupo_baile.php?grado_id=<?= $fila['grado_id'] ?>"
                                   class="btn-editar">
                                    Calificar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($resultado->num_rows === 0): ?>
                        <tr><td colspan="4">Aún no hay grados aprobados en baile grupal.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>