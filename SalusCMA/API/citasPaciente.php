<?php
header("Content-Type: application/json");
require_once("../conexion.php");

$id = $_GET["id"];

$sql = "SELECT c.*, con.nombre AS consultorio
        FROM citas c
        INNER JOIN consultorio con ON con.id_consultorio = c.id_consultorio
        WHERE c.id_paciente = ?
        ORDER BY c.fecha ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

$citas = [];
while ($row = $res->fetch_assoc()) {
    $citas[] = $row;
}

echo json_encode(["citas" => $citas]);
?>
