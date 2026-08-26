<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';
require_once 'verificar_token.php'; // Nuestro fiel guardia

// Solo Recursos puede cambiar contraseñas
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para cambiar contraseñas."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $datos = json_decode(file_get_contents("php://input"));

    if (!empty($datos->usuario_id) && !empty($datos->nueva_password)) {
        try {
            // Encriptamos la NUEVA contraseña
            $password_encriptada = password_hash($datos->nueva_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE usuarios SET password = :password WHERE id = :id";
            $stmt = $conexion->prepare($query);
            
            $stmt->bindParam(":password", $password_encriptada);
            $stmt->bindParam(":id", $datos->usuario_id);

            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["mensaje" => "La contraseña del usuario ha sido actualizada con éxito."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en el servidor: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Falta el usuario_id o la nueva_password."]);
    }
}
?>