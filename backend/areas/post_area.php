<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php'; // Candado de seguridad

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    // Validamos que el nombre no venga vacío
    if (!empty($datos->nombre)) {
        try {
            $query = "INSERT INTO areas (nombre) VALUES (:nombre)";
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(':nombre', $datos->nombre);
            
            if ($stmt->execute()) {
                http_response_code(201); // 201 = Creado
                echo json_encode(["mensaje" => "Área registrada exitosamente."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al guardar el área: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "El nombre del área es obligatorio."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Usa POST."]);
}
?>