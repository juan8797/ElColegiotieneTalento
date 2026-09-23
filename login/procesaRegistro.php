<?php
include '../conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre']);
    $apellido  = trim($_POST['apellido']);
    $correo    = strtolower(trim($_POST['correo']));
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol       = $_POST['rol'];

    // Validar formato de correo y que termine en .com
    if (!preg_match('/^[^\s@]+@[^\s@]+\.com$/i', $correo)) {
        echo "El correo debe ser válido y terminar en .com";
        exit();
    }

    // Validar grado
    $grado_id = !empty($_POST['grado_id']) ? $_POST['grado_id'] : NULL;

    if (($rol === 'estudiante' || $rol === 'docente') && empty($grado_id)) {
        echo "Debes seleccionar un grado";
        exit();
    }

    // Verificar que el correo no esté ya registrado
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $existe = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($existe) {
        echo "Ya existe un usuario registrado con ese correo.";
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
