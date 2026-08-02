<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/menu_admin.php'; ?>

<main class="container-fluid">
    <div class="bienvenida-admin">
        <h2>Bienvenido, Administrador</h2>
        <p>Desde este panel puedes gestionar todos los aspectos del Festival El Colegio Tiene Talento.</p>
    </div>

    <div class="tarjetas-admin">
        <div class="tarjeta-admin">
            <h3>📋 Grados y Grupos</h3>
            <p>Administra los grados y grupos disponibles para el registro. Puedes agregar nuevos grados, editarlos o eliminarlos. Los estudiantes y docentes seleccionan su grado al momento de registrarse.</p>
            <a href="/ElColegiotieneTalento/paneles/panel_admin_grados.php" class="btn-admin">Ir a Grados</a>
        </div>

        <div class="tarjeta-admin">
            <h3>👥 Usuarios</h3>
            <p>Visualiza todos los usuarios registrados en el sistema organizados por rol. Puedes ver los estudiantes con su grado asignado, los docentes con el grado que tienen a cargo y los jurados del festival.</p>
            <a href="/ElColegiotieneTalento/paneles/usuarios_admin.php" class="btn-admin">Ir a Usuarios</a>
        </div>
    </div>
</main>

<?php include '../includes/PiePagina.php'; ?>

</body>
</html>