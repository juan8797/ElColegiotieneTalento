<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

<?php include '../../includes/menu_admin.php'; ?>

<main class="container-fluid">
    <div class="bienvenida-admin">
        <h2>Bienvenido, Administrador</h2>
        <p>Desde este panel puedes gestionar todos los aspectos del Festival El Colegio Tiene Talento.</p>
    </div>

    <div class="tarjetas-admin">
        <div class="tarjeta-admin">
            <h3>📋 Grados y Grupos</h3>
            <p>Administra los grados y grupos disponibles para el registro. Puedes agregar nuevos grados, editarlos o eliminarlos. Los estudiantes y docentes seleccionan su grado al momento de registrarse.</p>
            <a href="/ElColegiotieneTalento/paneles/admin/Panel_admin_grados.php" class="btn-admin">Ir a Grados</a>
        </div>

        <div class="tarjeta-admin">
            <h3>👥 Usuarios</h3>
            <p>Visualiza todos los usuarios registrados en el sistema organizados por rol. Puedes ver los estudiantes con su grado asignado, los docentes con el grado que tienen a cargo y los jurados del festival.</p>
            <a href="/ElColegiotieneTalento/paneles/admin/panel_admin_usuarios.php" class="btn-admin">Ir a Usuarios</a>
        </div>

        <div class="tarjeta-admin">
            <h3>⭐ Criterios de Calificación</h3>
            <p>Define y gestiona los criterios y porcentajes de ponderación que utilizarán los jurados para evaluar las presentaciones de Bailes Grupales.</p>
            <a href="/ElColegiotieneTalento/paneles/admin/panel_criterios.php" class="btn-admin">Ir a Criterios</a>
        </div>

        <div class="tarjeta-admin">
            <h3>🖼️ Secciones del Inicio</h3>
            <p>Edita el título, la descripción y la imagen de las tarjetas de Bailes Grupales, Talento Individual y Talento Deportivo que se muestran en la página principal del sitio.</p>
            <a href="/ElColegiotieneTalento/paneles/admin/panel_secciones_index.php" class="btn-admin">Ir a Secciones</a>
        </div>

        <div class="tarjeta-admin">
            <h3>📢 Información Institucional</h3>
            <p>Publica avisos, cambios del festival o información importante del colegio (no necesariamente relacionada con el festival) para que aparezca en la página principal.</p>
            <a href="/ElColegiotieneTalento/paneles/admin/panel_informacion.php" class="btn-admin">Ir a Información</a>
        </div>
    </div>
</main>

<?php include '../../includes/PiePagina.php'; ?>

</body>
</html>