<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

$error = null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_admin.php'; ?>

    <main class="container-fluid">
        <div class="encabezado-panel">
            <h2>Agregar Nuevo Administrador</h2>
        </div>
        <div class="explanation-table">
            <p class="text-explanation">
                Crea una cuenta adicional con permisos de administrador, como respaldo
                en caso de que el administrador principal no pueda acceder al sistema.
            </p>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <p class="mensaje-admin">
                <?php if ($_GET['error'] === 'correo_repetido'): ?>
                    Ya existe un usuario registrado con ese correo.
                    <?php elseif ($_GET['error'] === 'formato_correo'): ?>
                        El correo debe ser válido y terminar en .com
                        <?php else: ?>
                            Ocurrió un error al crear el administrador. Intenta de nuevo.
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                        
        <div class="form-admin">
            <form action="procesar_agregar_admin.php" method="POST">
                <label for="nombre">Nombre:</label><br>
                <input type="text" name="nombre" id="nombre" required><br><br>

                <label for="apellido">Apellido:</label><br>
                <input type="text" name="apellido" id="apellido" required><br><br>

                <label for="correo">Correo:</label><br>
                <input type="email" name="correo" id="correo" required><br><br>

                <label for="contrasena">Contraseña:</label><br>
                <input type="password" name="contrasena" id="contrasena" required minlength="6"><br><br>

                <button type="submit">Crear Administrador</button>
            </form>
        </div>
    </main>

    <?php include '../../includes/PiePagina.php'; ?>
</body>
</html>