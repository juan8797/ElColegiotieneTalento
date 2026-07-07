<?php
session_start();
include 'conexion/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Estudiante</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="encabezado-panel">
        <h1>Bienvenido, <?php echo $_SESSION['nombre']; ?></h1>
        <a href="editarPerfil.php"><button class="btn-editar">Editar Perfil</button></a>
    </div>
    <a href="index.php"><button>inicio</button></a>
    <div class="Container-box">
        <div class="box-1">
            <h2>¿En qué consiste el festival?</h2>
            <p>El Festival de Talentos es un espacio donde los estudiantes pueden mostrar sus habilidades
                en tres modalidades diferentes. Puedes participar en una o en varias al mismo tiempo.</p>
            </div>
            <div class="box-2">
                <h3>Talento Individual</h3>
                <p>En esta modalidad cada estudiante muestra una habilidad personal única ante el público y el jurado.
                    Puede ser canto, baile solista, magia, comedia, poesía, teatro, un instrumento musical, o cualquier
                    otra habilidad que quieras destacar. El participante actúa solo y tiene un tiempo determinado para
                    su presentación. También deberás ponerle un nombre a tu acto para que el jurado pueda identificarlo.</p>
                </div>
                <div class="box-3">
                    <h3>Demostración Deportiva</h3>
                    <p>Esta modalidad está pensada para quienes destacan en alguna disciplina deportiva o física.
                        Puedes hacer una exhibición de fútbol, baloncesto, artes marciales, atletismo, gimnasia, patinaje,
                        o cualquier deporte que practiques. El objetivo es mostrar tu destreza, técnica y pasión por
                        el deporte frente a toda la comunidad del colegio.</p>
                    </div>
                    <div class="box-4">
                        <h3>Baile Grupal</h3>
                        <p>En esta categoría participan grupos de estudiantes que preparan una coreografía para presentar
                            en el festival. Puede ser cualquier ritmo o estilo: salsa, urbano, folclor, k-pop, reggaeton,
                            entre otros. Es una oportunidad de trabajo en equipo, coordinación y expresión artística
                            colectiva. El grupo debe inscribirse y presentarse unido el día del festival.</p>
                        </div>
    </div>
                        <hr>
<div class="formulario-participacion">
    <h2>Registra tu participación</h2>
    <div class="cuerpo-formulario">
        <p>Selecciona la o las modalidades en las que deseas participar:</p>
        <form method="POST" action="procesarParticipacion.php">
            
            <input type="checkbox" name="modalidades[]" value="talento_individual" id="talento">
            <label for="talento">Talento individual</label>
            <br>
            
            <input type="checkbox" name="modalidades[]" value="demostracion_deportiva" id="deportiva">
            <label for="deportiva">Demostración deportiva</label>
            <br>
            
            <input type="checkbox" name="modalidades[]" value="baile_grupal" id="baile">
            <label for="baile">Baile grupal</label>
            <br><br>
            
            <label for="nombre_acto">Nombre de tu acto (solo si elegiste Talento individual):</label>
            <br>
            <input type="text" name="nombre_acto" id="nombre_acto">
            <br><br>
            
            <button type="submit">Guardar participación</button>
            
        </form>
    </div>
</div>
</body>
</html>