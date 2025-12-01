<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require_once("../conexion.php");

echo json_encode([
    "test" => "Conexión exitosa",
    "variable_conexion" => isset($conn) ? "conn existe" : (isset($conexion) ? "conexion existe" : "ninguna existe")
]);
?>