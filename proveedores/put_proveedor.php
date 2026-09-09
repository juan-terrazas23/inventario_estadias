<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Seguridad: Solo el rol de recursos puede modificar
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para modificar proveedores."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $datos = json_decode(file_get_contents("php://input"));

    // Exigimos el ID para saber a quién modificar, además de los campos obligatorios
    if (!empty($datos->id) && !empty($datos->empresa) && !empty($datos->contacto) && !empty($datos->telefono)) {
        try {
            $query = "UPDATE proveedores 
                      SET empresa = :empresa, contacto = :contacto, telefono = :telefono, correo = :correo, direccion = :direccion 
                      WHERE id = :id";
            
            $stmt = $conexion->prepare($query);
            
            // Campos opcionales
            $correo = !empty($datos->correo) ? $datos->correo : null;
            $direccion = !empty($datos->direccion) ? $datos->direccion : null;

            $stmt->bindParam(":empresa", $datos->empresa);
            $stmt->bindParam(":contacto", $datos->contacto);
            $stmt->bindParam(":telefono", $datos->telefono);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":direccion", $direccion);
            $stmt->bindParam(":id", $datos->id);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["mensaje" => "Los datos del proveedor se actualizaron exitosamente."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar proveedor: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["error" => "Faltan datos obligatorios. Se requiere id, empresa, contacto y teléfono."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta PUT."]);
}
?>