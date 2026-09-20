<?php
session_start();
include '../../conexion/db.php';

$id_estudiante = $_SESSION['id'];

$stmt = $conexion->prepare("SELECT g.nombre FROM usuarios u 
                            LEFT JOIN grados g ON u.grado_id = g.id 
                            WHERE u.id = ?");
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$grado = $stmt->get_result()->fetch_assoc();
$stmt->close();
$nombre_grado = $grado['nombre'] ?? 'Sin grado';

// Vemos en cuáles modalidades ya está registrado el estudiante
$modalidadesRegistradas = [];
$stmt = $conexion->prepare("SELECT modalidad FROM participaciones WHERE usuario_id = ?");
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$res = $stmt->get_result();
while ($fila = $res->fetch_assoc()) {
    $modalidadesRegistradas[] = $fila['modalidad'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Estudiante</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="encabezado-panel">
    <h1>Bienvenido, <?php echo $_SESSION['nombre']; ?></h1>
    <div class="lado-derecho-panel">
        <span class="recuadro-grado">Grado: <?= htmlspecialchars($nombre_grado) ?></span>
        <a href="../../usuarios/editarPerfil.php"><button class="btn-editar">Editar Perfil</button></a>
        <a href="../../login/login.php"><button class="btn-editar">Cerrar sesion</button></a>
    </div>
    </div>
    <div class="Container-box">
        <div class="box-1">
            <h2>¿En qué consiste el festival?</h2>
            <p>El Festival de Talentos es un espacio donde los estudiantes pueden mostrar sus habilidades
                en tres modalidades diferentes. Puedes participar en una o en varias al mismo tiempo.</p>
            </div>
            <div class="box-2">
                <h3>Talento Individual</h3>
                <p>En esta modalidad cada estudiante muestra una habilidad personal única ante el público y el jurado.</p>
                </div>
                <div class="box-3">
                    <h3>Demostración Deportiva</h3>
                    <p>Esta modalidad está pensada para quienes destacan en alguna disciplina deportiva o física.</p>
                    </div>
                    <div class="box-4">
                        <h3>Baile Grupal</h3>
                        <p>En esta categoría participan estudiantes por grado que preparan una coreografía para presentar
                            en el festival.</p>
                        </div>
    </div>
    <hr>

<div class="formulario-participacion tabla-estudiante-container">
    <h2>Registra tu participación</h2>
       <?php if (isset($_SESSION['mensaje'])): ?>
       <p class="mensaje-admin"><?= htmlspecialchars($_SESSION['mensaje']) ?></p>
       <?php unset($_SESSION['mensaje']); ?>
       <?php endif; ?>
    <div class="cuerpo-formulario">
        <p>Selecciona una modalidad para registrarte. Puedes volver luego a registrar otra.</p>

        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th>Modalidad</th>
                    <th style="width: 170px; text-align: center;">Estado</th>
                    <th style="width: 160px;" class="col-accion">Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="texto-modalidad">Talento Individual</span></td>
                    <td style="text-align: center;">
                        <?php if (in_array('talento_individual', $modalidadesRegistradas)): ?>
                            <span class="estado-aprobado">&#10003; Ya registrado</span>
                        <?php else: ?>
                            <span class="estado-pendiente">Sin registrar</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-accion">
                        <?php if (!in_array('talento_individual', $modalidadesRegistradas)): ?>
                            <a href="seleccionar_categoria.php?modalidad=talento_individual" class="btn-admin btn-sm">Registrarme</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><span class="texto-modalidad">Demostración Deportiva</span></td>
                    <td style="text-align: center;">
                        <?php if (in_array('demostracion_deportiva', $modalidadesRegistradas)): ?>
                            <span class="estado-aprobado">&#10003; Ya registrado</span>
                        <?php else: ?>
                            <span class="estado-pendiente">Sin registrar</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-accion">
                        <?php if (!in_array('demostracion_deportiva', $modalidadesRegistradas)): ?>
                            <a href="seleccionar_categoria.php?modalidad=demostracion_deportiva" class="btn-admin btn-sm">Registrarme</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><span class="texto-modalidad">Baile Grupal</span></td>
                    <td style="text-align: center;">
                        <?php if (in_array('baile_grupal', $modalidadesRegistradas)): ?>
                            <span class="estado-aprobado">&#10003; Ya registrado</span>
                        <?php else: ?>
                            <span class="estado-pendiente">Sin registrar</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-accion">
                        <?php if (!in_array('baile_grupal', $modalidadesRegistradas)): ?>
                            <a href="confirmar_baile_grupal.php" class="btn-admin btn-sm">Registrarme</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/PiePagina.php'; ?>

</body>
</html>