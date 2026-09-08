<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jurado') {
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Jurado - Inicio</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_jurado.php'; ?>

    <div class="encabezado-panel">
        <h1>Bienvenido jurado, <?= $_SESSION['nombre'] ?></h1>
        <div class="lado-derecho-panel">
            <a href="../../usuarios/editarPerfil.php"><button class="btn-editar">Editar Perfil</button></a>
        </div>
    </div>

    <main class="container-fluid">
        <div class="explanation-table">
            <p class="text-explanation">
                Estimado jurado, <?= $_SESSION['nombre'] ?>, desde este panel podrás evaluar
                las dos modalidades del festival. Elige uno de los siguientes apartados para continuar.
            </p>
        </div>

        <section class="tarjetas-admin">

            <div class="tarjeta-admin">
                <h3>Talento Individual</h3>
                <p>
                    Aquí podrás ver a los estudiantes aprobados por su docente en la
                    modalidad de talento individual, y dejar una observación o consejo
                    para cada participante.
                </p>
                <a href="Panel_jurado_talento_individual.php" class="btn-admin">Ir al apartado</a>
            </div>

            <div class="tarjeta-admin">
                <h3>Baile Grupal</h3>
                <p>
                    Aquí podrás calificar a cada grado participante en la modalidad de
                    baile grupal, según los criterios definidos por el administrador,
                    y ayudar a definir el ganador de cada categoría.
                </p>
                <a href="Panel_jurado_baile_grupal.php" class="btn-admin">Ir al apartado</a>
            </div>

        </section>
    </main>
</body>
</html>