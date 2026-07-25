<?php
include '../conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $_POST['nombre'];
    $apellido  = $_POST['apellido'];
    $correo    = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol       = $_POST['rol'];

    // Validar grado
    $grado_id = !empty($_POST['grado_id']) ? $_POST['grado_id'] : NULL;

    if (($rol === 'estudiante' || $rol === 'docente') && empty($grado_id)) {
        echo "Debes seleccionar un grado";
        exit();
    }

    // INSERT con consulta preparada
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, grado_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nombre, $apellido, $correo, $contrasena, $rol, $grado_id);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        header("Location: ../login/login.php");
        exit();
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>
