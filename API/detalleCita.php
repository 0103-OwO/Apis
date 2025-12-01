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
            "error" => "ID de cita requerido",
            "cita" => null
        ]);
        exit;
    }

    $id = intval($_GET["id"]);

    $sql = "SELECT 
                c.id_cita,
                c.fecha,
                c.hora,
                c.estado,
                c.descripcion,
                c.id_paciente,
                c.id_medico,
                c.id_consultorio,
                p.nombre AS nombre_paciente,
                p.curp AS curp_paciente,
                CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', t.apellido_materno) AS nombre_medico,
                e.especialidad AS especialidad,
                co.nombre AS consultorio
            FROM citas c
            INNER JOIN pacientes p ON c.id_paciente = p.id_pacientes
            INNER JOIN trabajadores t ON c.id_medico = t.id_trabajador
            LEFT JOIN especialidad e ON t.id_especialidad = e.id_especialidad
            INNER JOIN consultorio co ON c.id_consultorio = co.id_consultorio
            WHERE c.id_cita = ?";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "cita" => $row
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Cita no encontrada",
            "cita" => null
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage(),
        "cita" => null
    ]);
}

ob_end_flush();
?>