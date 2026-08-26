<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos los datos del formulario de registro
    $datos = json_decode(file_get_contents("php://input"));

    if (!empty($datos->nombre) && !empty($datos->correo) && !empty($datos->password)) {
        try {
// DEFENSA 1: Verificar si el NOMBRE ya existe en la base de datos
            $checkQuery = "SELECT id FROM usuarios WHERE nombre = :nombre";
            $checkStmt = $conexion->prepare($checkQuery);
            $checkStmt->bindParam(":nombre", $datos->nombre);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                http_response_code(400); 
                echo json_encode(["error" => "Ese nombre de usuario ya está ocupado. Elige otro."]);
                exit();
            }

            // DEFENSA 2: Encriptar la contraseña (¡NUNCA guardar en texto plano!)
            $password_encriptada = password_hash($datos->password, PASSWORD_DEFAULT);
            
            // DEFENSA 3: Rol por defecto. Nadie se puede registrar como 'recursos' mágicamente.
            $rol_por_defecto = "almacen"; 

            // Guardamos al nuevo usuario
            $query = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (:nombre, :correo, :password, :rol)";
            $stmt = $conexion->prepare($query);
            
            $stmt->bindParam(":nombre", $datos->nombre);
            $stmt->bindParam(":correo", $datos->correo);
            $stmt->bindParam(":password", $password_encriptada);
            $stmt->bindParam(":rol", $rol_por_defecto);

            if($stmt->execute()) {
                http_response_code(201); // 201 = Creado
                echo json_encode(["mensaje" => "Usuario registrado exitosamente. Ya puedes iniciar sesión."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en el servidor: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos. Necesitas enviar nombre, correo y password."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta POST."]);
}
?>