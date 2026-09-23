<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

include '../../conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['nombre']);
    $apellido   = trim($_POST['apellido']);
    $correo     = strtolower(trim($_POST['correo']));

    if (!preg_match('/^[^\s@]+@[^\s@]+\.com$/i', $correo)) {
        header("Location: agregar_admin.php?error=formato_correo");
        exit();
    }

    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol        = 'admin'; // Fijo, no viene del formulario

    // Verificamos que el correo no esté ya en uso
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $existe = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($existe) {
        header("Location: agregar_admin.php?error=correo_repetido");
        exit();
    }

    // grado_id es NULL porque un admin no pertenece a ningún grado
    $stmt = $conexion->prepare(
        "INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol, grado_id) VALUES (?, ?, ?, ?, ?, NULL)"
    );
    $stmt->bind_param("sssss", $nombre, $apellido, $correo, $contrasena, $rol);
    $resultado = $stmt->execute();
    $stmt->close();

    if ($resultado) {
        $_SESSION['mensaje'] = "Administrador creado correctamente.";
        header("Location: panel_admin_usuarios.php");
        exit();
    } else {
        header("Location: agregar_admin.php?error=general");
        exit();
    }
} else {
    header("Location: agregar_admin.php");
    exit();
}