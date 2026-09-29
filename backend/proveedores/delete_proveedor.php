<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Seguridad: Solo el rol de recursos puede eliminar
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403); 
    echo json_encode(["error" => "No tienes permisos para dar de baja proveedores."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    
    // Leemos el ID desde la URL (ej. delete_proveedor.php?id=5)
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        
        try {
            // Borrado lógico (Desactivación)
            $query = "UPDATE proveedores SET activo = 0 WHERE id = :id";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":id", $id);
            
            if($stmt->execute()) {
                if($stmt->rowCount() > 0) {
                    http_response_code(200); 
                    echo json_encode(["mensaje" => "El proveedor fue dado de baja exitosamente."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No se encontró al proveedor indicado."]);
                }
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al intentar eliminar: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["error" => "Falta especificar el ID del proveedor."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta DELETE."]);
}
?>