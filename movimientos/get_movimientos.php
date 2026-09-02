<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';

try {
    $query = "SELECT m.id, p.nombre as producto, m.tipo, m.cantidad, m.fecha, m.motivo 
              FROM movimientos m 
              INNER JOIN productos p ON m.producto_id = p.id 
              ORDER BY m.fecha DESC";

    $stmt = $conexion->prepare($query);
    $stmt->execute();

    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($movimientos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Hubo un problema al obtener el historial: " . $e->getMessage()]);
}
?>