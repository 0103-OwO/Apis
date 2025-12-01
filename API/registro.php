<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("conexion.php");
ob_clean();

try {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON inválido");
    }
    
    $curp = strtoupper(trim($data["curp"] ?? ""));
    $nombre = trim($data["nombre"] ?? "");
    $apellido_paterno = trim($data["apellido_paterno"] ?? "");
    $apellido_materno = trim($data["apellido_materno"] ?? "");
    $fecha_nacimiento = trim($data["fecha_nacimiento"] ?? "");
    $email = strtolower(trim($data["email"] ?? ""));
    $usuario = trim($data["usuario"] ?? "");
    $contrasena = trim($data["contrasena"] ?? "");
    
    if (empty($curp) || empty($nombre) || empty($apellido_paterno) || 
        empty($apellido_materno) || empty($fecha_nacimiento) || 
        empty($email) || empty($usuario) || empty($contrasena)) {
        echo json_encode([
            "status" => "error",
            "msg" => "Todos los campos son obligatorios"
        ]);
        exit;
    }
    
    if (strlen($curp) != 18) {
        echo json_encode([
            "status" => "error",
            "msg" => "El CURP debe tener 18 caracteres"
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "status" => "error",
            "msg" => "El correo electrónico no es válido"
        ]);
        exit;
    }

    $sql = "SELECT id_pacientes FROM pacientes WHERE curp = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $curp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            "status" => "error",
            "msg" => "El CURP ya está registrado"
        ]);
        exit;
    }

    $sql = "SELECT id_usuario_cliente FROM usuarios_clientes WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            "status" => "error",
            "msg" => "El nombre de usuario ya está en uso"
        ]);
        exit;
    }

    $sql = "SELECT id_usuario_cliente FROM usuarios_clientes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            "status" => "error",
            "msg" => "El correo electrónico ya está registrado"
        ]);
        exit;
    }

    $conn->begin_transaction();
    
    try {
        $sql = "INSERT INTO pacientes (curp, nombre, apellido_paterno, apellido_materno, fecha_nacimiento) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $curp, $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento);
        $stmt->execute();
        
        $id_paciente = $conn->insert_id;
        
        $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);
        
        $id_rol = 19;
        $sql = "INSERT INTO usuarios_clientes (email, contrasena, id_rol, id_paciente, usuario) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiis", $email, $contrasena_hash, $id_rol, $id_paciente, $usuario);
        $stmt->execute();
        
        $id_usuario_cliente = $conn->insert_id;

        $conn->commit();
        
        echo json_encode([
            "status" => "ok",
            "msg" => "Registro exitoso. Ya puedes iniciar sesión.",
            "id_paciente" => $id_paciente,
            "id_usuario_cliente" => $id_usuario_cliente
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "msg" => "Error en el servidor: " . $e->getMessage()
    ]);
}

ob_end_flush();
?>