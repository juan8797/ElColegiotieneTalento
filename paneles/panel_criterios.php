<?php
session_start();

require_once '../conexion/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login/login.php');
    exit();
}

$sql = "SELECT id, nombre_criterio, valor FROM criterios ORDER BY id ASC";
$resultado = $conexion->query($sql);

$suma_total = 0;
$criterios = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $criterios[] = $fila;
        $suma_total += $fila['valor'];
    }
}

$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criterios de Calificación — Panel Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <?php include '../includes/menu_admin.php'; ?>

    <main class="container-fluid">
        <div class="encabezado-panel">
            <h2>Criterios de Calificación — Bailes Grupales</h2>
            <div class="lado-derecho-panel">
                <a href="agregar_criterio.php" class="btn-admin">+ Agregar nuevo criterio</a>
            </div>
        </div>

        <div class="explanation-table">
            <p class="text-explanation">
                Administre los criterios de evaluación y ponderación para la modalidad de <strong>Bailes Grupales</strong>. 
                Los jurados utilizarán estos criterios para calificar las presentaciones. Para que la evaluación sea válida, la suma total de los criterios debe ser exactamente del <strong>100%</strong>.
            </p>
        </div>

        <?php if ($mensaje): ?>
            <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <div class="resumen-criterios <?= (abs($suma_total - 100) < 0.01) ? 'correcto' : 'alerta' ?>">
            <div class="resumen-info">
                <span>Suma total actual:</span>
                <strong><?= number_format($suma_total, 2) ?>%</strong>
            </div>
            <div class="resumen-estado">
                <?php if (abs($suma_total - 100) < 0.01): ?>
                    <span class="badge-estado-ok">✅ Configuración completa (100%)</span>
                <?php elseif ($suma_total < 100): ?>
                    <span class="badge-estado-alerta">⚠️ Falta <?= number_format(100 - $suma_total, 2) ?>% para completar el 100%</span>
                <?php else: ?>
                    <span class="badge-estado-alerta">⚠️ Supera el 100% por <?= number_format($suma_total - 100, 2) ?>%</span>
                <?php endif; ?>
            </div>
        </div>

        <section class="seccion-usuarios">
            <h3>Listado de Criterios Registrados</h3>
            <table class="tabla-participaciones">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nombre del Criterio</th>
                        <th style="width: 170px; text-align: center;">Ponderación</th>
                        <th style="width: 220px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($criterios)): ?>
                        <?php foreach ($criterios as $c): ?>
                            <tr>
                                <td><strong>#<?= $c['id'] ?></strong></td>
                                <td><?= htmlspecialchars($c['nombre_criterio']) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge-porcentaje"><?= number_format($c['valor'], 2) ?>%</span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="editar_criterio.php?id=<?= $c['id'] ?>" class="btn-editar">Editar</a>
                                    <a href="confirmar_eliminar_criterio.php?id=<?= $c['id'] ?>" class="btn-eliminar">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 25px; color: var(--color-text-muted);">
                                No hay criterios registrados aún. Haga clic en <strong>"+ Agregar nuevo criterio"</strong> para comenzar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <?php include '../includes/PiePagina.php'; ?>

</body>
</html>