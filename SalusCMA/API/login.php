<?php
header("Content-Type: application/json");
require_once("../conexion.php");

$data = json_decode(file_get_contents("php://input"), true);

$usuario = $data["usuario"] ?? "";
$contrasena = $data["contrasena"] ?? "";

$response = [];

$sql = "SELECT u.*, r.nombre AS rol_nombre
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        WHERE u.usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    if ($row["contrasena"] === $contrasena) {

        // Obtener datos del trabajador
        $sql2 = "SELECT * FROM trabajadores WHERE id_trabajador = ?";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bind_param("i", $row["id_trabajador"]);
        $stmt2->execute();
        $trabajador = $stmt2->get_result()->fetch_assoc();

        $response = [
            "status" => "ok",
            "tipo" => "medico",
            "id" => $row["id_usuario"],
            "nombre" => $trabajador["nombre"],
            "rol" => $row["id_rol"],
            "token" => uniqid()
        ];

        echo json_encode($response);
        exit;
    }
}

// --- 2. Buscar en usuarios_clientes (pacientes) ---
$sql = "SELECT uc.*, r.nombre AS rol_nombre, p.nombre AS paciente_nombre
        FROM usuarios_clientes uc
        INNER JOIN rol r ON uc.id_rol = r.id_rol
        INNER JOIN pacientes p ON uc.id_paciente = p.id_pacientes
        WHERE uc.usuario_cliente = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    if ($row["contrasena"] === $contrasena) {
        $response = [
            "status" => "ok",
            "tipo" => "paciente",
            "id" => $row["id_paciente"],
            "nombre" => $row["paciente_nombre"],
            "rol" => $row["id_rol"],
            "token" => uniqid()
        ];

        echo json_encode($response);
        exit;
    }
}

// Si no se encontró usuario
echo json_encode(["status" => "error", "msg" => "Credenciales incorrectas"]);
?>
