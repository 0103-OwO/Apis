<?php
header("Content-Type: application/json");
require_once("../conexion.php");

$sql = "SELECT * FROM footer LIMIT 1";
$result = $conexion->query($sql);

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["status" => "error"]);
}
?>
