<?php include '../conexion/db.php'; ?><?php include '../conexion/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include '../includes/menu.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="recuadro card-header text-center">
                        <h4 class="Color-text">Registro</h4>
                    </div>
                    <div class="card-body">
                        <form action="/ElColegiotieneTalento/login/procesaRegistro.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Nombre:</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Apellido:</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo:</label>
                                <input type="email" name="correo" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña:</label>
                                <input type="password" name="contrasena" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rol:</label>
                                <select name="rol" class="form-control" required>
                                    <option value="estudiante">Estudiante</option>
                                    <option value="docente">Docente</option>
                                    <option value="jurado">Jurado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Grado y Grupo:</label>
                                <select name="grado_id" class="form-control">
                                    <option value="">Selecciona un grado (solo estudiantes y docentes)</option>
                                    <?php
                                    $grados = $conexion->query("SELECT * FROM grados ORDER BY nombre ASC");
                                    while ($g = $grados->fetch_assoc()):
                                    ?>
                                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Registrarme</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/PiePagina.php'; ?>

</body>
</html>