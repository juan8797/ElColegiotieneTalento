<?php
session_start();
include 'conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['id'];
    $modalidades = $_POST['modalidades'] ?? [];
    $nombre_acto = $_POST['nombre_acto'] ?? null;

    foreach ($modalidades as $modalidad) {
        if ($modalidad === 'talento_individual') {
            $sql = "INSERT INTO participaciones (usuario_id, modalidad, nombre_acto) 
                    VALUES ('$usuario_id', '$modalidad', '$nombre_acto')";
        } else {
            $sql = "INSERT INTO participaciones (usuario_id, modalidad, nombre_acto) 
                    VALUES ('$usuario_id', '$modalidad', NULL)";
        }
        $conexion->query($sql);
    }

    header("Location: Panel_estudiantes.php");
    exit();
}