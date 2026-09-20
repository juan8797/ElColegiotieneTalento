<?php
session_start();
include '../../conexion/db.php';

$id_estudiante = $_SESSION['id'];

// Evitar doble registro
$stmt = $conexion->prepare("SELECT id FROM participaciones WHERE usuario_id = ? AND modalidad = 'baile_grupal'");
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: Panel_estudiantes.php");
    exit();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Baile Grupal</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="encabezado-panel">
        <h1>Registrar participación — Baile Grupal</h1>
    </div>

    <main class="container-fluid">
        <div class="card-confirmacion-estudiante">
            <div class="card-confirmacion-header">
                <h2>Modalidad Baile Grupal</h2>
                <span class="badge-modalidad-grupal">Presentación por Grado</span>
            </div>
            <div class="card-confirmacion-body">
                <div class="confirmacion-info-box">
                    <p class="confirmacion-destacado">
                        Vas a registrarte en la modalidad de <strong>Baile Grupal</strong> representando a tu grado escolar.
                    </p>
                    <p class="confirmacion-detalle">
                        Esta modalidad no requiere elegir una categoría individual porque la coreografía se evalúa de manera colectiva por grado.
                    </p>
                </div>

                <form method="POST" action="../../usuarios/guardar_participacion.php">
                    <input type="hidden" name="modalidad" value="baile_grupal">
                    <div class="confirmacion-acciones">
                        <a href="Panel_estudiantes.php" class="btn-volver">Cancelar</a>
                        <button type="submit" class="btn-primary btn-confirmar-participacion">Confirmar participación</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include '../../includes/PiePagina.php'; ?>
</body>
</html>