<?php
// 1. Cabeceras - Permitimos el método DELETE
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';

// 2. Verificamos que el método sea DELETE
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    
    // 3. Atrapamos el ID que viene en la URL (usando $_GET)
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        
        try {
            // 4. Preparamos el DELETE (¡Siempre con el WHERE!)
            $query = "DELETE FROM productos WHERE id = :id";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":id", $id);
            
            // 5. Ejecutamos
            if($stmt->execute()) {
                if($stmt->rowCount() > 0) {
                    http_response_code(200); 
                    echo json_encode(["mensaje" => "Producto eliminado exitosamente."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No se encontró ningún producto con ese ID."]);
                }
            }
        } catch(PDOException $e) {
            // Un error muy común aquí es si intentas borrar un producto que ya tiene movimientos registrados
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar el producto: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["error" => "Falta el ID. Debes enviarlo en la URL (ej. ?id=1)."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta DELETE."]);
}
?>