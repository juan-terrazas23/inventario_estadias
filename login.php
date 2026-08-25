<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    if (!empty($datos->correo) && !empty($datos->password)) {
        try {
            // ¡NUEVO! Agregamos la palabra "rol" a la consulta
            $query = "SELECT id, nombre, correo, password, rol FROM usuarios WHERE correo = :correo";
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":correo", $datos->correo);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($datos->password, $usuario['password'])) {
                
                $token = bin2hex(random_bytes(32)); 
                
                $updateQuery = "UPDATE usuarios SET token = :token WHERE id = :id";
                $updateStmt = $conexion->prepare($updateQuery);
                $updateStmt->bindParam(":token", $token);
                $updateStmt->bindParam(":id", $usuario['id']);
                $updateStmt->execute();

                http_response_code(200);
                echo json_encode([
                    "mensaje" => "Login exitoso",
                    "token" => $token,
                    "usuario" => [
                        "id" => $usuario['id'],
                        "nombre" => $usuario['nombre'],
                        "correo" => $usuario['correo'],
                        "rol" => $usuario['rol'] // ¡NUEVO! Le enviamos el rol al frontend
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["error" => "Correo o contraseña incorrectos."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en el servidor: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Por favor envía el correo y el password."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Solo se acepta POST."]);
}
?>