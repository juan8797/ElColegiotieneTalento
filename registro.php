<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'menu.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="recuadro card-header text-center">
                    <h4 class="Color-text">Registro</h4>
                </div>
                <div class="card-body">
                    <form action="login/procesaRegistro.php" method="post">
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
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Registrarme</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>