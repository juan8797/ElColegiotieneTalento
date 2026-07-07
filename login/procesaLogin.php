<?php
session_start();
include '../conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo    = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT * FROM usuarios WHERE correo = '$correo'";
    $result = $conexion->query($sql);

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        if (password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['id']     = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol']    = $usuario['rol'];

            if ($usuario['rol'] === 'docente') {
            header("Location: ../Panel_docentes.php");
            } elseif ($usuario['rol'] === 'jurado') {
                header("Location: ../Panel_jurado.php");
                } else {
                    header("Location: ../Panel_estudiantes.php");
            }
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
}
?>