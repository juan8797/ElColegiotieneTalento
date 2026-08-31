<?php
session_start();

require_once '../conexion/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login/login.php');
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: panel_criterios.php');
    exit();
}

$stmt = $conexion->prepare("SELECT id, nombre_criterio, valor FROM criterios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$criterio = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$criterio) {
    header('Location: panel_criterios.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Eliminación — Panel Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <?php include '../includes/menu_admin.php'; ?>

    <main class="container-fluid">
        <div class="seccion-usuarios card-confirmar-eliminar">
            <div class="icono-peligro">⚠️</div>
            <h2>Confirmar Eliminación</h2>

            <p class="texto-confirmar">
                ¿Está seguro de que desea eliminar el criterio <strong>"<?= htmlspecialchars($criterio['nombre_criterio']) ?>"</strong> con una ponderación del <strong><?= number_format($criterio['valor'], 2) ?>%</strong>?
            </p>

            <div class="alerta error" style="margin: 20px 0;">
                Esta acción no se puede deshacer. Si el criterio ya tiene calificaciones registradas, el sistema protegerá la integridad de los datos impidiendo su eliminación.
            </div>

            <form method="POST" action="eliminar_criterio.php">
                <input type="hidden" name="id" value="<?= (int)$criterio['id'] ?>">
                <div class="botones-editar" style="justify-content: center; margin-top: 25px;">
                    <button type="submit" class="btn-eliminar" style="font-size: 15px; padding: 10px 22px;">Sí, eliminar criterio</button>
                    <a href="panel_criterios.php" class="btn-admin" style="background-color: var(--color-primary);">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php include '../includes/PiePagina.php'; ?>

</body>
</html>