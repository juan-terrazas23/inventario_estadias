<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';

try {
    // Traemos las áreas para llenar el select (ej. para registrar una Salida)
    $query = "SELECT id, nombre FROM areas ORDER BY nombre ASC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode($areas);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener áreas: " . $e->getMessage()]);
}
?>