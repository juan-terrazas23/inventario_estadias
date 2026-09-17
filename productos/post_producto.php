<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    // Validar que manden los campos obligatorios
    if (!empty($datos->nombre) && isset($datos->categoria_id) && isset($datos->unidad_id)) {
        try {
            // Asignar valores por defecto si no vienen en el JSON
            $precio = isset($datos->precio) ? $datos->precio : 0.00;
            $stock = isset($datos->stock) ? $datos->stock : 0;
            $stock_minimo = isset($datos->stock_minimo) ? $datos->stock_minimo : 10;
            $stock_maximo = isset($datos->stock_maximo) ? $datos->stock_maximo : 100;

            // Inserción con las nuevas columnas de stock
            $query = "INSERT INTO productos (nombre, precio, stock, stock_minimo, stock_maximo, categoria_id, unidad_id, activo) 
                      VALUES (:nombre, :precio, :stock, :stock_minimo, :stock_maximo, :categoria_id, :unidad_id, 1)";
            
            $stmt = $conexion->prepare($query);
            
            $stmt->bindParam(':nombre', $datos->nombre);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindParam(':stock_minimo', $stock_minimo, PDO::PARAM_INT);
            $stmt->bindParam(':stock_maximo', $stock_maximo, PDO::PARAM_INT);
            $stmt->bindParam(':categoria_id', $datos->categoria_id, PDO::PARAM_INT);
            $stmt->bindParam(':unidad_id', $datos->unidad_id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            http_response_code(201);
            echo json_encode(["mensaje" => "Producto creado exitosamente."]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => "Error al guardar el producto: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos obligatorios (nombre, categoria_id, unidad_id)."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Usa POST."]);
}
?>