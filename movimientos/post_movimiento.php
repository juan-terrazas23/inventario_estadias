<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';
require_once 'verificar_token.php'; // <-- AQUÍ PONEMOS AL GUARDIA EN LA PUERTA

// ¡NUEVO NIVEL DE SEGURIDAD RBAC! 
// Si el usuario es legítimo pero es de almacén, lo rebotamos (403 = Prohibido)
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403); 
    echo json_encode(["error" => "No tienes permisos de Recursos para registrar proveedores."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    if (!empty($datos->producto_id) && !empty($datos->tipo) && !empty($datos->cantidad) && !empty($datos->motivo)) {

        try {
            $conexion->beginTransaction();

            $queryCheck = "SELECT stock, nombre, activo FROM productos WHERE id = :producto_id FOR UPDATE";
            $stmtCheck = $conexion->prepare($queryCheck);
            $stmtCheck->bindParam(":producto_id", $datos->producto_id);
            $stmtCheck->execute();

            $productoActual = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$productoActual) {
                throw new Exception("El producto seleccionado no existe en la base de datos.");
            }

            if ($productoActual['activo'] == 0) {
                throw new Exception("No puedes registrar movimientos. El producto '" . $productoActual['nombre'] . "' está eliminado o descontinuado.");
            }

            if ($datos->tipo == 'salida' && $datos->cantidad > $productoActual['stock']) {
                throw new Exception("Stock insuficiente de " . $productoActual['nombre'] . ". Tienes " . $productoActual['stock'] . " y quieres sacar " . $datos->cantidad . ".");
            }

            $queryMov = "INSERT INTO movimientos (producto_id, tipo, cantidad, motivo) 
                         VALUES (:producto_id, :tipo, :cantidad, :motivo)";
            $stmtMov = $conexion->prepare($queryMov);

            $stmtMov->bindParam(":producto_id", $datos->producto_id);
            $stmtMov->bindParam(":tipo", $datos->tipo);
            $stmtMov->bindParam(":cantidad", $datos->cantidad);
            $stmtMov->bindParam(":motivo", $datos->motivo);
            $stmtMov->execute();

            if ($datos->tipo == 'entrada') {
                $queryStock = "UPDATE productos SET stock = stock + :cantidad WHERE id = :producto_id";
            } else if ($datos->tipo == 'salida') {
                $queryStock = "UPDATE productos SET stock = stock - :cantidad WHERE id = :producto_id";
            }

            $stmtStock = $conexion->prepare($queryStock);
            $stmtStock->bindParam(":cantidad", $datos->cantidad);
            $stmtStock->bindParam(":producto_id", $datos->producto_id);
            $stmtStock->execute();

            $conexion->commit();

            http_response_code(201);
            echo json_encode(["mensaje" => "Movimiento registrado y stock actualizado con éxito."]);

        } catch (Exception $e) {
            $conexion->rollBack();
            http_response_code(409);
            echo json_encode(["error" => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos. Necesitas producto_id, tipo, cantidad y motivo."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Solo se acepta POST."]);
}
?>