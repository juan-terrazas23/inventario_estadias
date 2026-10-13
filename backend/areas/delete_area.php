<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php'; // Candado de seguridad

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Para el método DELETE, el ID lo mandas por la URL (ej. ?id=5)
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        
        try {
            $query = "DELETE FROM areas WHERE id = :id";
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":id", $id);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(["mensaje" => "Área eliminada exitosamente."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No se encontró el área a eliminar."]);
                }
            }
        } catch(PDOException $e) {
            // Si MySQL rechaza el borrado (porque esa área ya tiene salidas en el historial), 
            // manda un 500 y el Frontend mostrará tu mensaje personalizado.
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar el área por integridad de datos."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Falta el ID del área a eliminar."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta DELETE."]);
}
?>