<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jurado') {
    header("Location: ../login/login.php");
    exit();
}

include '../../conexion/db.php';

// Paso 1: puntaje ponderado por jurado y por grado
// Paso 2: promedio de esos puntajes entre todos los jurados que calificaron ese grado
$sql = "SELECT g.id AS grado_id, g.nombre AS grado_nombre, g.categoria,
               AVG(sub.puntaje_jurado) AS promedio_final,
               COUNT(sub.jurado_id) AS num_jurados
        FROM grados g
        JOIN (
            SELECT c.grado_id, c.jurado_id,
                   SUM(c.puntaje * cr.valor / 100) AS puntaje_jurado
            FROM calificaciones c
            JOIN criterios cr ON c.criterio_id = cr.id
            GROUP BY c.grado_id, c.jurado_id
        ) sub ON sub.grado_id = g.id
        GROUP BY g.id, g.nombre, g.categoria
        ORDER BY g.categoria ASC, promedio_final DESC";

$resultado = $conexion->query($sql);

// Organizamos los resultados agrupados por categoría, ya ordenados de mayor a menor puntaje
$categorias = [];
while ($fila = $resultado->fetch_assoc()) {
    $cat = $fila['categoria'] ?? 'sin_categoria';
    $categorias[$cat][] = $fila;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Jurado - Ranking y Ganadores</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_jurado.php'; ?>

    <div class="encabezado-panel">
        <h1>Ranking y Ganadores - Baile Grupal</h1>
        <div class="lado-derecho-panel">
            <a href="../usuarios/editarPerfil.php"><button class="btn-editar">Editar Perfil</button></a>
        </div>
    </div>

    <main class="container-fluid">
        <div class="explanation-table">
            <p class="text-explanation">
                Aquí puedes ver el puntaje promedio de cada grado (calculado entre todos los
                jurados que ya calificaron), organizado por categoría. El primer lugar de
                cada categoría se resalta como ganador.
            </p>
        </div>

        <?php if (empty($categorias)): ?>
            <section class="seccion-usuarios">
                <p>Aún no hay calificaciones registradas para mostrar un ranking.</p>
            </section>
        <?php endif; ?>

        <?php foreach ($categorias as $nombre_categoria => $grados): ?>
            <section class="seccion-usuarios">
                <h3><?= htmlspecialchars(ucfirst($nombre_categoria)) ?></h3>
                <table class="tabla-participaciones">
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Grado</th>
                            <th>Puntaje promedio</th>
                            <th>Jurados que calificaron</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grados as $index => $g): ?>
                            <tr>
                                <td>
                                    <?= $index + 1 ?>
                                    <?php if ($index === 0): ?>
                                        <span class="estado-aprobado">🏆 Ganador</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($g['grado_nombre']) ?></td>
                                <td><?= number_format($g['promedio_final'], 2) ?></td>
                                <td><?= (int)$g['num_jurados'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>
    </main>
</body>
</html>