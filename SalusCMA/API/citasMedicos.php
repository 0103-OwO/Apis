<?php
header("Content-Type: application/json");
require_once("../conexion.php");

$id = $_GET["id"];

$sql = "SELECT c.*, p.nombre AS paciente
        FROM citas c
        INNER JOIN pacientes p ON p.id_pacientes = c.id_paciente
        WHERE c.id_medico = ?
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
