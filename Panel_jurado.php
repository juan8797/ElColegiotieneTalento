<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>
<body>
    <h1>Juarados</h1>
    <?php
session_start();
echo "Bienvenido jurado: " . $_SESSION['nombre'];
?>
<div>
    <a href="editarPerfil.php" class="btn btn-danger"><button>Editar Perfil</button></a>
</div>
</body>
</html>