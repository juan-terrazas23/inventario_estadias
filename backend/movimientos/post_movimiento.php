<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Validar que solo personal autorizado (almacen o recursos) pueda registrar movimientos
if ($usuario_auth['rol'] !== 'almacen' && $usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para registrar movimientos de inventario."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    // Validar los campos obligatorios principales
    if (!empty($datos->producto_id) && !empty($datos->tipo) && isset($datos->cantidad) && !empty($datos->persona_responsable)) {
        try {
            $conexion->beginTransaction();

            $producto_id = (int)$datos->producto_id;
            $tipo = strtolower(trim($datos->tipo)); // 'entrada' o 'salida'
            $cantidad = (int)$datos->cantidad;
            $persona_responsable = trim($datos->persona_responsable);
            $motivo = !empty($datos->motivo) ? trim($datos->motivo) : 'Sin motivo especificado';

            // Validar campos opcionales según sea entrada o salida
            $area_id = !empty($datos->area_id) ? (int)$datos->area_id : null;
            $proveedor_id = !empty($datos->proveedor_id) ? (int)$datos->proveedor_id : null;

            if ($tipo === 'salida' && !$area_id) {
                throw new Exception("Para una salida de material es obligatorio especificar el área de destino.");
            }

            if ($tipo === 'entrada' && !$proveedor_id) {
                throw new Exception("Para una entrada de material es obligatorio especificar el proveedor.");
            }

            // 1. Verificar stock actual del producto
            $queryStock = "SELECT stock FROM productos WHERE id = :producto_id FOR UPDATE";
            $stmtStock = $conexion->prepare($queryStock);
            $stmtStock->bindParam(":producto_id", $producto_id, PDO::PARAM_INT);
            $stmtStock->execute();
            $producto = $stmtStock->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("El producto seleccionado no existe.");
            }

            $stockActual = (int)$producto['stock'];

            // 2. Calcular nuevo stock y validar si hay suficientes piezas en salidas
            if ($tipo === 'salida') {
                if ($stockActual < $cantidad) {
                    throw new Exception("Stock insuficiente. Stock actual disponible: " . $stockActual);
                }
                $nuevoStock = $stockActual - $cantidad;
            } else if ($tipo === 'entrada') {
                $nuevoStock = $stockActual + $cantidad;
            } else {
                throw new Exception("Tipo de movimiento inválido. Use 'entrada' o 'salida'.");
            }

            // 3. Actualizar el stock en la tabla productos
            $queryUpdate = "UPDATE productos SET stock = :nuevo_stock WHERE id = :producto_id";
            $stmtUpdate = $conexion->prepare($queryUpdate);
            $stmtUpdate->bindParam(":nuevo_stock", $nuevoStock, PDO::PARAM_INT);
            $stmtUpdate->bindParam(":producto_id", $producto_id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // 4. Registrar el movimiento en el historial inalterable con la persona responsable
            $queryMovimiento = "INSERT INTO movimientos (producto_id, tipo, cantidad, area_id, proveedor_id, persona_responsable, motivo, fecha) 
                                VALUES (:producto_id, :tipo, :cantidad, :area_id, :proveedor_id, :persona_responsable, :motivo, NOW())";
            
            $stmtMovimiento = $conexion->prepare($queryMovimiento);
            $stmtMovimiento->bindParam(":producto_id", $producto_id, PDO::PARAM_INT);
            $stmtMovimiento->bindParam(":tipo", $tipo);
            $stmtMovimiento->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $stmtMovimiento->bindParam(":area_id", $area_id, PDO::PARAM_INT);
            $stmtMovimiento->bindParam(":proveedor_id", $proveedor_id, PDO::PARAM_INT);
            $stmtMovimiento->bindParam(":persona_responsable", $persona_responsable);
            $stmtMovimiento->bindParam(":motivo", $motivo);
            $stmtMovimiento->execute();

            // Confirmar transacción exitosa
            $conexion->commit();

            http_response_code(201);
            echo json_encode([
                "mensaje" => "Movimiento registrado con éxito en el historial.",
                "nuevo_stock" => $nuevoStock,
                "persona_responsable" => $persona_responsable
            ]);

        } catch (Exception $e) {
            $conexion->rollBack();
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos obligatorios (producto_id, tipo, cantidad, persona_responsable)."]);
    }
}
?>