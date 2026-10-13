<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php'; // Candado de seguridad

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $datos = json_decode(file_get_contents("php://input"));

    // Verificamos que venga tanto el ID (para saber cuál modificar) como el nuevo nombre
    if (!empty($datos->id) && !empty($datos->nombre)) {
        try {
            $query = "UPDATE areas SET nombre = :nombre WHERE id = :id";
            $stmt = $conexion->prepare($query);
            
            $stmt->bindParam(":nombre", $datos->nombre);
            $stmt->bindParam(":id", $datos->id);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(["mensaje" => "Área actualizada exitosamente."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No se encontró el área o el nombre es el mismo que ya tenía."]);
                }
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo actualizar el área: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos. El ID y el nombre son obligatorios."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta PUT."]);
}
?>