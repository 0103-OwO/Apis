<?php
$host = "localhost";      
$db   = "u941347256_SalusCMA";      
$user = "u941347256_Equipo3";        
$pass = "Equipo3.DSML";      

$conn = new mysqli($host, $user, $pass, $db);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "msg" => "Error de conexión a BD"]);
    exit;
}


?>