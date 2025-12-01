<?php
ob_start();
ini_set('display_errors', 0);      
error_reporting(0);

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once("conexion.php");
ob_clean();

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $usuario = trim($data["usuario"] ?? "");
    $contrasena = $data["contrasena"] ?? "";

    if (empty($usuario) || empty($contrasena)) {
        echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
        exit;
    }

    // Buscar en tabla usuario (médicos/trabajadores)
    $sql = "SELECT u.*, r.nombre AS rol_nombre
            FROM usuario u
            INNER JOIN rol r ON u.id_rol = r.id_rol
            WHERE u.usuario = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($contrasena, $row["contrasena"])) {
            $sql2 = "SELECT nombre FROM trabajadores WHERE id_trabajador = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $row["id_trabajador"]);
            $stmt2->execute();
            $trabajador = $stmt2->get_result()->fetch_assoc();
            
            echo json_encode([
                "status" => "ok",
                "tipo" => "medico",
                "id" => $row["id_trabajador"],
                "nombre" => $trabajador["nombre"] ?? "Empleado",
                "rol" => $row["id_rol"],
                "token" => bin2hex(random_bytes(16))
            ]);
            exit;
        }
    }
    
    // Buscar en tabla usuarios_clientes (pacientes)
    // IMPORTANTE: Devolver p.id_pacientes, NO uc.id_paciente
    $sql = "SELECT uc.*, r.nombre AS rol_nombre, 
            p.nombre AS paciente_nombre, 
            p.id_pacientes AS id_paciente_real
            FROM usuarios_clientes uc
            INNER JOIN rol r ON uc.id_rol = r.id_rol
            INNER JOIN pacientes p ON uc.id_paciente = p.id_pacientes
            WHERE uc.usuario = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($contrasena, $row["contrasena"])) {
            echo json_encode([
                "status" => "ok",
                "tipo" => "paciente",
                "id" => $row["id_paciente_real"], // ← ESTE ES EL CAMBIO IMPORTANTE
                "nombre" => $row["paciente_nombre"],
                "rol" => $row["id_rol"],
                "token" => bin2hex(random_bytes(16))
            ]);
            exit;
        }
    }
    
    echo json_encode(["status" => "error", "msg" => "Credenciales incorrectas"]);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "msg" => "Error del servidor: " . $e->getMessage()
    ]);
}

ob_end_flush();
?>