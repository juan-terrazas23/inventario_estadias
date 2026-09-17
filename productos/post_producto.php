<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Permiso para crear productos (almacen o recursos)
if ($usuario_auth['rol'] !== 'almacen' && $usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para crear productos."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    if (!empty($datos->nombre) && !empty($datos->categoria_id) && isset($datos->precio) && isset($datos->stock)) {
        try {
            // LÓGICA NUEVA: Si no mandan unidad de medida desde el front, por defecto asignamos el ID 1 (Pieza)
            $unidad_id = isset($datos->unidad_id) ? $datos->unidad_id : 1;
            
            // Asignamos imagen por defecto si no envían una
            $imagen = !empty($datos->imagen) ? $datos->imagen : 'default.jpg';

            $query = "INSERT INTO productos (nombre, precio, stock, categoria_id, unidad_id, imagen, activo) 
                      VALUES (:nombre, :precio, :stock, :categoria_id, :unidad_id, :imagen, 1)";
            $stmt = $conexion->prepare($query);

            $stmt->bindParam(":nombre", $datos->nombre);
            $stmt->bindParam(":precio", $datos->precio);
            $stmt->bindParam(":stock", $datos->stock);
            $stmt->bindParam(":categoria_id", $datos->categoria_id);
            $stmt->bindParam(":unidad_id", $unidad_id);
            $stmt->bindParam(":imagen", $imagen);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["mensaje" => "Producto creado con éxito."]);
            } else {
                throw new Exception("Error al insertar el producto.");
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en BD: " . $e->getMessage()]);
        } catch(Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos obligatorios (nombre, categoria, precio, stock)."]);
    }
}
?>