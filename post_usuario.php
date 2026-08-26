<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';
require_once 'verificar_token.php'; // Llamamos al guardia

// Bloqueamos a almacén
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para crear nuevos empleados."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    // Ahora exigimos que desde el frontend manden qué ROL va a tener el nuevo usuario
    if (!empty($datos->nombre) && !empty($datos->correo) && !empty($datos->password) && !empty($datos->rol)) {
        
        try {
            // Verificar si el NOMBRE o el CORREO ya están ocupados
            $checkQuery = "SELECT id FROM usuarios WHERE nombre = :nombre OR correo = :correo";
            $checkStmt = $conexion->prepare($checkQuery);
            $checkStmt->bindParam(":nombre", $datos->nombre);
            $checkStmt->bindParam(":correo", $datos->correo);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                http_response_code(400); 
                echo json_encode(["error" => "El nombre de usuario o el correo ya están ocupados."]);
                exit();
            }

            // Encriptamos la contraseña
            $password_encriptada = password_hash($datos->password, PASSWORD_DEFAULT);
            
            // Insertamos al nuevo empleado con el rol que eligió el administrador
            $query = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (:nombre, :correo, :password, :rol)";
            $stmt = $conexion->prepare($query);
            
            $stmt->bindParam(":nombre", $datos->nombre);
            $stmt->bindParam(":correo", $datos->correo);
            $stmt->bindParam(":password", $password_encriptada);
            $stmt->bindParam(":rol", $datos->rol);

            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["mensaje" => "Empleado creado exitosamente en el sistema."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en el servidor: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos. Se requiere nombre, correo, password y rol."]);
    }
}
?>