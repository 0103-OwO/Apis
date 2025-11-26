<?php
header("Content-Type: application/json");
require_once("../conexion.php");

$sql = "SELECT * FROM imagenes";
$result = $conexion->query($sql);

$imagenes = [];

while ($row = $result->fetch_assoc()) {
    $imagenes[] = $row;
}

echo json_encode(["imagenes" => $imagenes]);
?>
