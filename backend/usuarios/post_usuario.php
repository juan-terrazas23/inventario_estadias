<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    // Validar que vengan todos los campos necesarios
    if (!empty($datos->nombre) && !empty($datos->correo) && !empty($datos->password) && !empty($datos->rol)) {
        try {
            // Encriptar la contraseña de forma segura
            $password_hash = password_hash($datos->password, PASSWORD_BCRYPT);
            
            // Limpiar el texto del rol que nos envían
            $rol_texto = strtolower(trim($datos->rol));
            
            // Lógica para asignar el rol_id correcto basado en el texto enviado
            $rol_id_asignado = 2; // Por defecto asignamos almacen (2) por seguridad
            
            if ($rol_texto === 'recursos') {
                $rol_id_asignado = 1;
            } elseif ($rol_texto === 'almacen') {
                $rol_id_asignado = 2;
            } else {
                throw new Exception("El rol especificado no es válido. Usa 'recursos' o 'almacen'.");
            }

            // Inserción en la base de datos coordinando la palabra con su ID numérico
            $query = "INSERT INTO usuarios (nombre, correo, password, rol, activo, rol_id) 
                      VALUES (:nombre, :correo, :password, :rol, 1, :rol_id)";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(':nombre', $datos->nombre);
            $stmt->bindParam(':correo', $datos->correo);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':rol', $rol_texto); 
            $stmt->bindParam(':rol_id', $rol_id_asignado, PDO::PARAM_INT);
            
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                "mensaje" => "Usuario creado exitosamente.",
                "rol_asignado" => $rol_texto,
                "rol_id_asignado" => $rol_id_asignado
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => "Error al crear usuario: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos obligatorios (nombre, correo, password, rol)."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Usa POST."]);
}
?>