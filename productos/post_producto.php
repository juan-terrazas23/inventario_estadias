<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Permitir que tanto 'recursos' como 'almacen' puedan registrar productos nuevos
if ($usuario_auth['rol'] !== 'recursos' && $usuario_auth['rol'] !== 'almacen') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos suficientes para registrar productos."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    if (!empty($datos->nombre)) {
        // Valores por defecto por si no se envían opcionales
        $precio = isset($datos->precio) ? $datos->precio : 0;
        $stock = isset($datos->stock) ? $datos->stock : 0;
        $categoria_id = isset($datos->categoria_id) ? $datos->categoria_id : 1;

        try {
            $query = "INSERT INTO productos (nombre, precio, stock, categoria_id, activo) 
                      VALUES (:nombre, :precio, :stock, :categoria_id, 1)";

            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":nombre", $datos->nombre);
            $stmt->bindParam(":precio", $precio);
            $stmt->bindParam(":stock", $stock);
            $stmt->bindParam(":categoria_id", $categoria_id);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["mensaje" => "Producto registrado con éxito en el catálogo."]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "No se pudo guardar el producto en la base de datos."]);
            }

        } catch (PDOException $e) {
            http_response_code(509);
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Falta la descripción del producto."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido."]);
}
?>