<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once("conexion.php");
ob_clean();

try {
    if (!isset($_GET["id"]) || empty($_GET["id"])) {
        echo json_encode([
            "success" => false,
            "error" => "ID de médico requerido",
            "citas" => [],
            "total" => 0
        ]);
        exit;
    }

    $id = intval($_GET["id"]);

    // Consulta completa con todos los datos necesarios
    $sql = "SELECT 
                c.id_cita,
                c.fecha,
                c.hora,
                c.id_paciente,
                c.id_medico,
                c.id_consultorio,
                p.nombre AS nombre_paciente,
                p.curp AS curp_paciente,
                co.nombre AS consultorio
            FROM citas c
            INNER JOIN pacientes p ON c.id_paciente = p.id_pacientes
            INNER JOIN consultorio co ON c.id_consultorio = co.id_consultorio
            WHERE c.id_medico = ?
            ORDER BY c.fecha ASC, c.hora ASC";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    $citas = [];
    while ($row = $res->fetch_assoc()) {
        $citas[] = $row;
    }

    echo json_encode([
        "success" => true,
        "citas" => $citas,
        "total" => count($citas)
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage(),
        "citas" => [],
        "total" => 0
    ]);
}

ob_end_flush();
?>