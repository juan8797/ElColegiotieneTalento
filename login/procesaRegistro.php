<?php
include '../conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $_POST['nombre'];
    $apellido  = $_POST['apellido'];
    $correo    = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol       = $_POST['rol'];

    $sql = "INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) 
            VALUES ('$nombre', '$apellido', '$correo', '$contrasena', '$rol')";
    
    $result = $conexion->query($sql);

    if ($result) {
        header("Location: ../login.php");
        exit();
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>