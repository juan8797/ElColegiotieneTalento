<?php
session_start();

require_once '../conexion/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login/login.php');
    exit();
}

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: panel_criterios.php');
    exit();
}

$stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM calificaciones WHERE criterio_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$tiene_calificaciones = $stmt->get_result()->fetch_assoc()['total'] > 0;
$stmt->close();

if ($tiene_calificaciones) {
    $_SESSION['mensaje'] = "No se puede eliminar: este criterio ya tiene calificaciones registradas.";
} else {
    $stmt = $conexion->prepare("DELETE FROM criterios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['mensaje'] = "Criterio eliminado correctamente.";
}

header('Location: panel_criterios.php');
exit();