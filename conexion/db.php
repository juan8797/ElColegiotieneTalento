<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "festival_talentos";

$conexion = new mysqli($host, $username, $password, $database);

if ($conexion->connect_error) {
    die("Error de conexion:". $conexion->connect_error);
}
?> 