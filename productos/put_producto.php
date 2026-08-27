<?php
// 1. Cabeceras - Ahora permitimos el método PUT
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
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

// 2. Verificamos que el método sea PUT
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    
    $datos_recibidos = file_get_contents("php://input");
    $datos = json_decode($datos_recibidos);

    // 3. Verificamos que venga el ID (obligatorio para saber a quién actualizar) y los demás datos
    if (!empty($datos->id) && !empty($datos->nombre) && !empty($datos->precio) && !empty($datos->categoria_id)) {
        
        try {
            // 4. Preparamos el UPDATE (¡El WHERE id = :id es la parte más importante!)
            $query = "UPDATE productos 
                      SET nombre = :nombre, precio = :precio, categoria_id = :categoria_id 
                      WHERE id = :id";
            
            $stmt = $conexion->prepare($query);
            
            // 5. Vinculamos las variables
            $stmt->bindParam(":nombre", $datos->nombre);
            $stmt->bindParam(":precio", $datos->precio);
            $stmt->bindParam(":categoria_id", $datos->categoria_id);
            $stmt->bindParam(":id", $datos->id);
            
            // 6. Ejecutamos
            if($stmt->execute()) {
                // Comprobamos si realmente se modificó alguna fila (por si mandan un ID que no existe)
                if($stmt->rowCount() > 0) {
                    http_response_code(200); // 200 = OK
                    echo json_encode(["mensaje" => "Producto actualizado exitosamente."]);
                } else {
                    http_response_code(404); // 404 = Not Found
                    echo json_encode(["error" => "No se encontró el producto o los datos son iguales a los actuales."]);
                }
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo actualizar el producto: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["error" => "Faltan datos. El ID, nombre, precio y categoria_id son obligatorios."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta PUT."]);
}
?>