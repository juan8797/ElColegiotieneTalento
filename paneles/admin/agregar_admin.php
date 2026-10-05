<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

$mensaje_error = null;

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'correo_repetido') {
        $mensaje_error = 'Ya existe un usuario registrado con ese correo.';
    } elseif ($_GET['error'] === 'formato_correo') {
        $mensaje_error = 'El correo debe ser válido y terminar en .com';
    } else {
        $mensaje_error = 'Ocurrió un error al crear el administrador. Intenta de nuevo.';
    }
}
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

        <div class="contenido-form-admin">

            <div class="explanation-table">
                <p class="text-explanation">
                    Crea una cuenta adicional con permisos de administrador, como respaldo
                    en caso de que el administrador principal no pueda acceder al sistema.
                </p>
            </div>

            <?php if ($mensaje_error !== null): ?>
                <p class="mensaje-admin mensaje-error" role="alert">
                    <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <div class="form-admin">
                <form action="procesar_agregar_admin.php" method="POST" class="form-admin-grid">

                    <div class="campo">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>

                    <div class="campo">
                        <label for="apellido">Apellido</label>
                        <input type="text" name="apellido" id="apellido" class="form-control" required>
                    </div>

                    <div class="campo campo-completo">
                        <label for="correo">Correo</label>
                        <input type="email" name="correo" id="correo" class="form-control" autocomplete="off" required>
                        <small class="ayuda-campo">Debe ser válido y terminar en .com</small>
                    </div>

                    <div class="campo campo-completo">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" name="contrasena" id="contrasena" class="form-control" autocomplete="new-password" required minlength="6">
                        <small class="ayuda-campo">Mínimo 6 caracteres</small>
                    </div>

                    <div class="acciones-form">
                        <button type="submit">Crear Administrador</button>
                    </div>

                </form>
            </div>

        </div>
    </main>

    <?php include '../../includes/PiePagina.php'; ?>
</body>
</html>