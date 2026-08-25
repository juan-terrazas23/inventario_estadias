<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        
        try {
            // ¡EL CAMBIO MAESTRO! No borramos, solo actualizamos el estatus a 0
            $query = "UPDATE productos SET activo = 0 WHERE id = :id";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":id", $id);
            
            if($stmt->execute()) {
                if($stmt->rowCount() > 0) {
                    http_response_code(200); 
                    echo json_encode(["mensaje" => "Producto eliminado (desactivado) exitosamente. Su historial se mantiene intacto."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No se encontró el producto."]);
                }
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["error" => "Falta el ID."]);
    }
}
?>