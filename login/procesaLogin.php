<?php
session_start();
include '../conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo     = strtolower(trim($_POST['correo']));
    $contrasena = $_POST['contrasena'];

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        if (password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['id']     = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol']    = $usuario['rol'];

            if ($usuario['rol'] === 'docente') {
                header("Location: ../paneles/Panel_docentes.php");
            } elseif ($usuario['rol'] === 'jurado') {
                header("Location: ../paneles/jurado/Panel_jurado_inicio.php");
            } elseif ($usuario['rol'] === 'admin') {
                header("Location: ../paneles/admin/panel_admin_principal.php");
            } else {
                header("Location: ../paneles/estudiante/Panel_estudiantes.php");
            }
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }

    $stmt->close();
}