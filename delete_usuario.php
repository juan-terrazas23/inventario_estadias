<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';
require_once 'verificar_token.php'; // Nuestro guardia

// 1. Verificamos que sea el administrador de recursos
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para dar de baja empleados."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    
    // Como es DELETE, solemos mandar el ID por la URL (ej. ?id=3)
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id_a_eliminar = $_GET['id'];
        
        // DEFENSA: Evitar que el administrador se borre a sí mismo
        if ($id_a_eliminar == $usuario_auth['id']) {
            http_response_code(400);
            echo json_encode(["error" => "Por seguridad, no puedes eliminar tu propia cuenta de administrador."]);
            exit();
        }
        
        try {
            // Hacemos el Borrado Lógico (cambiamos activo a 0)
            $query = "UPDATE usuarios SET activo = 0 WHERE id = :id";
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":id", $id_a_eliminar);
            
            if($stmt->execute()) {
                if($stmt->rowCount() > 0) {
                    http_response_code(200); 
                    echo json_encode(["mensaje" => "El usuario ha sido dado de baja del sistema exitosamente."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No se encontró al usuario."]);
                }
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["error" => "Falta especificar el ID del usuario a eliminar."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Solo se acepta el método DELETE."]);
}
?>