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
    if (!isset($_GET["idCita"]) || empty($_GET["idCita"])) {
        echo json_encode([
            "success" => false,
            "error" => "ID de Cita requerido",
            "historial" => null
        ]);
        exit;
    }

    $idCita = intval($_GET["idCita"]);

    $sql = "SELECT 
                h.id_historial,
                h.id_cita,
                h.tension_arterial,
                h.peso,
                h.talla,
                h.temperatura,
                h.descripcion,
                p.nombre AS nombre_paciente
            FROM historial h
            INNER JOIN citas c ON h.id_cita = c.id_cita
            INNER JOIN pacientes p ON c.id_paciente = p.id_pacientes
            WHERE h.id_cita = ?";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $idCita);
    $stmt->execute();
    $res = $stmt->get_result();

    $historial = $res->fetch_assoc();

    echo json_encode([
        "success" => true,
        "historial" => $historial, 
        "error" => null
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage(),
        "historial" => null
    ]);
}

ob_end_flush();
?>