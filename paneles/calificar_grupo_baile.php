<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jurado') {
    header("Location: ../login/login.php");
    exit();
}

include '../conexion/db.php';

$grado_id = $_GET['grado_id'] ?? $_POST['grado_id'] ?? null;
if (!$grado_id || !is_numeric($grado_id)) {
    header("Location: Panel_jurado_baile_grupal.php");
    exit();
}

$jurado_id = $_SESSION['id'];
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $puntajes = $_POST['puntaje'] ?? [];
    $huboError = false;

    foreach ($puntajes as $criterio_id => $puntaje) {
        if (!is_numeric($puntaje) || $puntaje < 0 || $puntaje > 10) {
            $huboError = true;
            continue;
        }

        $sql = "INSERT INTO calificaciones (jurado_id, grado_id, criterio_id, puntaje)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE puntaje = VALUES(puntaje)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiid", $jurado_id, $grado_id, $criterio_id, $puntaje);
        $stmt->execute();
        $stmt->close();
    }

    $mensaje = $huboError
        ? "Se guardaron los puntajes válidos. Algunos valores estaban fuera de rango (0-10) y no se guardaron."
        : "Calificación guardada correctamente.";
}

$stmt = $conexion->prepare("SELECT nombre FROM grados WHERE id = ?");
$stmt->bind_param("i", $grado_id);
$stmt->execute();
$grado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$grado) {
    header("Location: Panel_jurado_baile_grupal.php");
    exit();
}

$sql = "SELECT cr.id, cr.nombre_criterio, cr.valor,
               (SELECT puntaje FROM calificaciones c
                WHERE c.criterio_id = cr.id AND c.grado_id = ? AND c.jurado_id = ?) AS puntaje_actual
        FROM criterios cr
        ORDER BY cr.id ASC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $grado_id, $jurado_id);
$stmt->execute();
$criterios = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar - <?= htmlspecialchars($grado['nombre']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <?php include '../includes/menu_jurado.php'; ?>

    <div class="encabezado-panel">
        <h1>Calificar: <?= htmlspecialchars($grado['nombre']) ?></h1>
        <div class="lado-derecho-panel">
            <a href="Panel_jurado_baile_grupal.php"><button class="btn-editar">Volver</button></a>
        </div>
    </div>

    <main class="container-fluid">

        <?php if ($mensaje): ?>
            <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <div class="explanation-table">
            <p class="text-explanation">
                Asigna un puntaje de 0 a 10 para cada criterio. El puntaje ponderado final
                se calcula automáticamente según el peso (%) de cada criterio.
            </p>
        </div>

        <section class="form-admin">
            <form method="POST" action="calificar_grupo_baile.php">
                <input type="hidden" name="grado_id" value="<?= (int)$grado_id ?>">

                <table class="tabla-participaciones">
                    <thead>
                        <tr>
                            <th>Criterio</th>
                            <th>Peso</th>
                            <th>Puntaje (0-10)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($c = $criterios->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['nombre_criterio']) ?></td>
                                <td><?= number_format($c['valor'], 2) ?>%</td>
                                <td>
                                    <input type="number"
                                           name="puntaje[<?= $c['id'] ?>]"
                                           min="0" max="10" step="0.1"
                                           value="<?= htmlspecialchars($c['puntaje_actual'] ?? '') ?>"
                                           required>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <button type="submit" class="btn-guardar">Guardar Calificación</button>
            </form>
        </section>
    </main>
</body>
</html>