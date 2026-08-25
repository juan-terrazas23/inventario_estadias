<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'conexion.php';

try {
    // Solo necesitamos el ID y el nombre para el menú desplegable
    $query = "SELECT id, nombre FROM categorias ORDER BY nombre ASC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode($categorias);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener categorías: " . $e->getMessage()]);
}
?>