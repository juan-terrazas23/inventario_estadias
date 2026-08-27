<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// ¡NUEVO NIVEL DE SEGURIDAD RBAC! 
// Si el usuario es legítimo pero es de almacén, lo rebotamos (403 = Prohibido)
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403); 
    echo json_encode(["error" => "No tienes permisos de Recursos para registrar proveedores."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos el JSON que manda el frontend
    $datos = json_decode(file_get_contents("php://input"));

    // Validamos que por lo menos vengan los datos obligatorios
    if (!empty($datos->empresa) && !empty($datos->contacto) && !empty($datos->telefono)) {
        
        try {
            $query = "INSERT INTO proveedores (empresa, contacto, telefono, correo, direccion) 
                      VALUES (:empresa, :contacto, :telefono, :correo, :direccion)";
            
            $stmt = $conexion->prepare($query);
            
            // Si no mandan correo o dirección, les ponemos un valor nulo para que no marque error
            $correo = !empty($datos->correo) ? $datos->correo : null;
            $direccion = !empty($datos->direccion) ? $datos->direccion : null;

            $stmt->bindParam(":empresa", $datos->empresa);
            $stmt->bindParam(":contacto", $datos->contacto);
            $stmt->bindParam(":telefono", $datos->telefono);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":direccion", $direccion);
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["mensaje" => "Proveedor registrado exitosamente."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al registrar proveedor: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["error" => "Faltan datos obligatorios. Se requiere empresa, contacto y teléfono."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta POST."]);
}
?>