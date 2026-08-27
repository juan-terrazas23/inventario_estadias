<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'conexion.php';

try {
    $query = "SELECT m.id, p.nombre as producto, m.tipo, m.cantidad, m.fecha, m.motivo 
              FROM movimientos m 
              INNER JOIN productos p ON m.producto_id = p.id 
              ORDER BY m.fecha DESC"; // Los más nuevos primero
              
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($movimientos);

} catch(PDOException $e) {
    echo json_encode(["error" => "Hubo un problema al obtener el historial: " . $e->getMessage()]);
}
?>