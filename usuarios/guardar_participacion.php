<?php
session_start();
include '../conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../paneles/Panel_estudiantes.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$modalidad = $_POST['modalidad'] ?? null;
$categoria_id = $_POST['categoria_id'] ?? null;
$nombre_acto = $_POST['nombre_acto'] ?? null;

if (!in_array($modalidad, ['talento_individual', 'demostracion_deportiva', 'baile_grupal'])) {
    header("Location: ../paneles/Panel_estudiantes.php");
    exit();
}

$stmt = $conexion->prepare(
    "INSERT INTO participaciones (usuario_id, modalidad, categoria_id, nombre_acto) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isis", $usuario_id, $modalidad, $categoria_id, $nombre_acto);

if (!$stmt->execute()) {
    // El UNIQUE KEY (usuario_id, modalidad) evita duplicados a nivel de base de datos
    $_SESSION['mensaje'] = "No se pudo registrar: ya tienes una participación en esa modalidad.";
} else {
    $_SESSION['mensaje'] = "Participación registrada correctamente.";
}
$stmt->close();

header("Location: ../paneles/estudiante/Panel_estudiantes.php");
exit();